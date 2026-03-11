<script setup lang="ts">
import { onMounted, onBeforeUnmount, ref } from 'vue'
import * as THREE from 'three'
import { GLTFLoader } from 'three/examples/jsm/loaders/GLTFLoader.js'
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js'

const props = withDefaults(
  defineProps<{
    modelPath?: string
    height?: string
    autoRotate?: boolean
  }>(),
  {
    modelPath: '/models/air_jordan_1.glb',
    height: '680px',
    autoRotate: true,
  },
)

const stageRef = ref<HTMLElement | null>(null)
const loading = ref(true)
const error = ref('')

let scene: THREE.Scene | null = null
let camera: THREE.PerspectiveCamera | null = null
let renderer: THREE.WebGLRenderer | null = null
let controls: OrbitControls | null = null
let modelRoot: THREE.Group | null = null
let frameId = 0
let resizeObserver: ResizeObserver | null = null
let resumeRotateTimer: number | null = null
let meshTargets: THREE.Object3D[] = []

const raycaster = new THREE.Raycaster()
const pointer = new THREE.Vector2()

const clearResumeTimer = () => {
  if (resumeRotateTimer !== null) {
    window.clearTimeout(resumeRotateTimer)
    resumeRotateTimer = null
  }
}

const setCanvasIdleState = () => {
  if (!renderer?.domElement) return
  renderer.domElement.style.cursor = 'grab'
  renderer.domElement.style.touchAction = 'pan-y'
}

const setCanvasDragState = () => {
  if (!renderer?.domElement) return
  renderer.domElement.style.cursor = 'grabbing'
  renderer.domElement.style.touchAction = 'none'
}

const pauseAutoRotate = () => {
  clearResumeTimer()
  if (controls) {
    controls.autoRotate = false
  }
  setCanvasDragState()
}

const resumeAutoRotate = () => {
  clearResumeTimer()
  setCanvasIdleState()

  if (!props.autoRotate) return

  resumeRotateTimer = window.setTimeout(() => {
    if (controls) {
      controls.autoRotate = true
    }
    setCanvasIdleState()
  }, 900)
}

const getIntersections = (event: PointerEvent) => {
  if (!renderer || !camera || !meshTargets.length) {
    return []
  }

  const rect = renderer.domElement.getBoundingClientRect()

  pointer.x = ((event.clientX - rect.left) / rect.width) * 2 - 1
  pointer.y = -((event.clientY - rect.top) / rect.height) * 2 + 1

  raycaster.setFromCamera(pointer, camera)

  return raycaster.intersectObjects(meshTargets, true)
}

const handlePointerDown = (event: PointerEvent) => {
  if (!controls) return

  const hitModel = getIntersections(event).length > 0

  if (hitModel) {
    controls.enabled = true
    pauseAutoRotate()
  } else {
    controls.enabled = false
    setCanvasIdleState()
  }
}

const handlePointerEnd = () => {
  if (!controls) return
  controls.enabled = true
  resumeAutoRotate()
}

const fitCameraToObject = (object: THREE.Object3D) => {
  if (!camera || !controls) return

  const box = new THREE.Box3().setFromObject(object)
  const size = box.getSize(new THREE.Vector3())
  const center = box.getCenter(new THREE.Vector3())

  object.position.sub(center)

  const maxDim = Math.max(size.x, size.y, size.z)
  const fov = (camera.fov * Math.PI) / 180
  const mobileView = window.innerWidth < 768
  const distanceFactor = mobileView ? 1.12 : 1.38
  const cameraZ = Math.abs(maxDim / 2 / Math.tan(fov / 2)) * distanceFactor

  camera.position.set(cameraZ * 0.18, cameraZ * 0.08, cameraZ)
  camera.near = Math.max(0.01, maxDim / 100)
  camera.far = Math.max(1000, maxDim * 25)
  camera.updateProjectionMatrix()

  controls.target.set(0, 0, 0)
  controls.minDistance = cameraZ
  controls.maxDistance = cameraZ
  controls.update()
}

const createLights = () => {
  if (!scene) return

  const ambient = new THREE.AmbientLight(0xffffff, 1.7)
  scene.add(ambient)

  const hemi = new THREE.HemisphereLight(0xffffff, 0xe5e7eb, 1.25)
  hemi.position.set(0, 20, 0)
  scene.add(hemi)

  const key = new THREE.DirectionalLight(0xffffff, 2.4)
  key.position.set(6, 8, 10)
  scene.add(key)

  const fill = new THREE.DirectionalLight(0xdbeafe, 1.05)
  fill.position.set(-7, 3, 6)
  scene.add(fill)

  const rim = new THREE.DirectionalLight(0xffffff, 1.35)
  rim.position.set(0, 7, -10)
  scene.add(rim)

  const low = new THREE.DirectionalLight(0xffffff, 0.45)
  low.position.set(0, -3, 5)
  scene.add(low)
}

const resize = () => {
  if (!stageRef.value || !camera || !renderer) return

  const width = stageRef.value.clientWidth
  const height = stageRef.value.clientHeight

  if (!width || !height) return

  camera.aspect = width / height
  camera.updateProjectionMatrix()
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2))
  renderer.setSize(width, height, false)
}

const animate = () => {
  if (!renderer || !scene || !camera) return

  frameId = window.requestAnimationFrame(animate)
  controls?.update()
  renderer.render(scene, camera)
}

const disposeScene = () => {
  clearResumeTimer()

  if (frameId) {
    window.cancelAnimationFrame(frameId)
    frameId = 0
  }

  if (renderer?.domElement) {
    renderer.domElement.removeEventListener('pointerdown', handlePointerDown)
  }

  window.removeEventListener('pointerup', handlePointerEnd)
  window.removeEventListener('pointercancel', handlePointerEnd)

  controls?.dispose()
  controls = null

  if (modelRoot) {
    modelRoot.traverse((child) => {
      const mesh = child as THREE.Mesh

      if (mesh.isMesh) {
        if (mesh.geometry) {
          mesh.geometry.dispose()
        }

        const materials = Array.isArray(mesh.material) ? mesh.material : [mesh.material]
        materials.forEach((material) => material?.dispose?.())
      }
    })
  }

  if (scene && modelRoot) {
    scene.remove(modelRoot)
  }

  modelRoot = null
  meshTargets = []

  renderer?.dispose()
  renderer = null
  camera = null
  scene = null

  resizeObserver?.disconnect()
  resizeObserver = null

  if (stageRef.value) {
    stageRef.value.innerHTML = ''
  }
}

onMounted(() => {
  if (!stageRef.value) return

  scene = new THREE.Scene()

  camera = new THREE.PerspectiveCamera(34, 1, 0.1, 1000)
  camera.position.set(0, 0.1, 4)

  renderer = new THREE.WebGLRenderer({
    antialias: true,
    alpha: false,
    powerPreference: 'high-performance',
  })

  renderer.outputColorSpace = THREE.SRGBColorSpace
  renderer.toneMapping = THREE.ACESFilmicToneMapping
  renderer.toneMappingExposure = 1.05
  renderer.setClearColor(0xffffff, 1)
  renderer.domElement.style.display = 'block'
  renderer.domElement.style.width = '100%'
  renderer.domElement.style.height = '100%'

  stageRef.value.appendChild(renderer.domElement)

  controls = new OrbitControls(camera, renderer.domElement)
  controls.enableDamping = true
  controls.dampingFactor = 0.075
  controls.rotateSpeed = 0.72
  controls.enableZoom = false
  controls.enablePan = false
  controls.autoRotate = props.autoRotate
  controls.autoRotateSpeed = 1.05
  controls.minPolarAngle = Math.PI / 2 - 0.62
  controls.maxPolarAngle = Math.PI / 2 + 0.62
  controls.minAzimuthAngle = -Infinity
  controls.maxAzimuthAngle = Infinity

  setCanvasIdleState()
  createLights()
  resize()

  renderer.domElement.addEventListener('pointerdown', handlePointerDown)
  window.addEventListener('pointerup', handlePointerEnd)
  window.addEventListener('pointercancel', handlePointerEnd)

  resizeObserver = new ResizeObserver(() => {
    resize()
    if (modelRoot) {
      fitCameraToObject(modelRoot)
    }
  })
  resizeObserver.observe(stageRef.value)

  const loader = new GLTFLoader()

  loader.load(
    props.modelPath,
    (gltf) => {
      if (!scene) return

      modelRoot = gltf.scene
      modelRoot.rotation.y = -0.85
      modelRoot.rotation.x = 0.08

      meshTargets = []

      modelRoot.traverse((child) => {
        const mesh = child as THREE.Mesh

        if (mesh.isMesh) {
          mesh.castShadow = false
          mesh.receiveShadow = false
          meshTargets.push(mesh)
        }
      })

      scene.add(modelRoot)
      fitCameraToObject(modelRoot)

      loading.value = false
      animate()
    },
    undefined,
    (loadError) => {
      console.error(loadError)
      error.value = 'Failed to load 3D model.'
      loading.value = false
    },
  )
})

onBeforeUnmount(() => {
  disposeScene()
})
</script>

<template>
  <section
    class="shoe-showcase relative w-full overflow-hidden bg-white text-[#111827]"
    :style="{ '--shoe-stage-height': height }"
  >
    <div class="pointer-events-none absolute inset-0">
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_left_top,rgba(17,24,39,0.04),transparent_28%)]" />
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_right_center,rgba(17,24,39,0.035),transparent_26%)]" />
      <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(17,24,39,0.02),transparent_18%,transparent_82%,rgba(17,24,39,0.02))]" />
    </div>

    <div class="relative mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-12 lg:px-8 lg:py-14">
      <div class="grid items-center gap-10 lg:grid-cols-[minmax(0,1.08fr)_minmax(0,0.92fr)] xl:gap-16">
        <div class="order-1">
          <div class="shoe-showcase__stage-wrap relative mr-auto w-full">
            <div ref="stageRef" class="shoe-showcase__stage" />

            <div
              v-if="loading"
              class="pointer-events-none absolute inset-0 z-20 flex items-center justify-center"
            >
              <div class="rounded-full border border-slate-200 bg-white/90 px-4 py-2 text-sm font-medium text-slate-700 shadow-sm">
                Loading 3D Model...
              </div>
            </div>

            <div
              v-if="error"
              class="absolute inset-0 z-30 flex items-center justify-center px-6 text-center"
            >
              <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700 shadow-sm">
                {{ error }}
              </div>
            </div>

         
          </div>
        </div>

        <div class="order-2">
          <div
            class="inline-flex items-center rounded-full border border-slate-200 bg-white px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500 shadow-sm sm:text-xs"
          >
            New Arrival
          </div>

          <h2
            class="mt-5 max-w-[11ch] text-[36px] font-semibold leading-[0.92] tracking-[-0.05em] text-slate-900 sm:text-[48px] lg:text-[58px] xl:text-[72px]"
          >
            Introducing Air Jordan 1
          </h2>

          <p class="mt-5 max-w-[60ch] text-[15px] leading-7 text-slate-600 sm:text-base sm:leading-8">
            Step into an iconic silhouette reimagined for a bold modern statement. The Air Jordan 1
            combines heritage design, standout details, and unmistakable attitude in a form that
            continues to define sneaker culture.
          </p>

        

          <!-- <div class="mt-8 flex flex-wrap items-center gap-3">
            <div
              class="inline-flex items-center rounded-full border border-slate-200 bg-white px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-600 shadow-sm sm:text-xs"
            >
              360° Preview
            </div>
            <div
              class="inline-flex items-center rounded-full border border-slate-200 bg-white px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-600 shadow-sm sm:text-xs"
            >
              Touch Enabled
            </div>
            <div
              class="inline-flex items-center rounded-full border border-slate-200 bg-white px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-600 shadow-sm sm:text-xs"
            >
              Premium Style
            </div>
          </div> -->
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.shoe-showcase__stage-wrap {
  width: 100%;
  max-width: 100%;
}

.shoe-showcase__stage {
  position: relative;
  width: 100%;
  height: clamp(420px, 104vw, 700px);
  overflow: hidden;
  border-radius: 0;
  border: 0;
  background: transparent;
  box-shadow: none;
}
.shoe-showcase__stage :deep(canvas) {
  display: block;
  width: 100% !important;
  height: 100% !important;
  outline: none;
  touch-action: pan-y;
}

@media (min-width: 1024px) {
  .shoe-showcase__stage-wrap {
    max-width: 760px;
  }

  .shoe-showcase__stage {
    height: var(--shoe-stage-height);
  }
}
</style>