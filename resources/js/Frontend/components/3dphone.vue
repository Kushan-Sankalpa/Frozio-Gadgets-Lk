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
    modelPath: '/models/iphone_17_pro_max.glb',
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

const clearResumeTimer = () => {
  if (resumeRotateTimer !== null) {
    window.clearTimeout(resumeRotateTimer)
    resumeRotateTimer = null
  }
}

const pauseAutoRotate = () => {
  clearResumeTimer()

  if (controls) {
    controls.autoRotate = false
  }

  if (renderer?.domElement) {
    renderer.domElement.style.cursor = 'grabbing'
  }
}

const resumeAutoRotate = () => {
  clearResumeTimer()

  if (!props.autoRotate) {
    if (renderer?.domElement) {
      renderer.domElement.style.cursor = 'grab'
    }
    return
  }

  resumeRotateTimer = window.setTimeout(() => {
    if (controls) {
      controls.autoRotate = true
    }

    if (renderer?.domElement) {
      renderer.domElement.style.cursor = 'grab'
    }
  }, 900)
}

const fitCameraToObject = (object: THREE.Object3D) => {
  if (!camera || !controls) return

  const box = new THREE.Box3().setFromObject(object)
  const size = box.getSize(new THREE.Vector3())
  const center = box.getCenter(new THREE.Vector3())

  object.position.sub(center)

  const maxDim = Math.max(size.x, size.y, size.z)
  const fov = (camera.fov * Math.PI) / 180
  const cameraZ = Math.abs(maxDim / 2 / Math.tan(fov / 2)) * 1.65

  camera.position.set(cameraZ * 0.22, cameraZ * 0.03, cameraZ)
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

  const ambient = new THREE.AmbientLight(0xffffff, 1.45)
  scene.add(ambient)

  const hemisphere = new THREE.HemisphereLight(0xffffff, 0x05070b, 1.3)
  hemisphere.position.set(0, 18, 0)
  scene.add(hemisphere)

  const key = new THREE.DirectionalLight(0xffffff, 2.4)
  key.position.set(5.5, 7.5, 9.5)
  scene.add(key)

  const fill = new THREE.DirectionalLight(0x9ec5ff, 1.15)
  fill.position.set(-7, 2.5, 6)
  scene.add(fill)

  const rim = new THREE.DirectionalLight(0xffffff, 1.6)
  rim.position.set(0, 7, -10)
  scene.add(rim)

  const low = new THREE.DirectionalLight(0xffffff, 0.65)
  low.position.set(0, -3, 6)
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

  if (controls) {
    controls.removeEventListener('start', pauseAutoRotate)
    controls.removeEventListener('end', resumeAutoRotate)
    controls.dispose()
    controls = null
  }

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
  camera.position.set(0, 0.12, 4)

  renderer = new THREE.WebGLRenderer({
    antialias: true,
    alpha: false,
    powerPreference: 'high-performance',
  })

  renderer.outputColorSpace = THREE.SRGBColorSpace
  renderer.toneMapping = THREE.ACESFilmicToneMapping
  renderer.toneMappingExposure = 1.08
  renderer.setClearColor(0x000000, 1)
  renderer.domElement.style.display = 'block'
  renderer.domElement.style.width = '100%'
  renderer.domElement.style.height = '100%'
  renderer.domElement.style.cursor = 'grab'

  stageRef.value.appendChild(renderer.domElement)

  controls = new OrbitControls(camera, renderer.domElement)
  controls.enableDamping = true
  controls.dampingFactor = 0.075
  controls.rotateSpeed = 0.75
  controls.enableZoom = false
  controls.enablePan = false
  controls.autoRotate = props.autoRotate
  controls.autoRotateSpeed = 1.35
  controls.minPolarAngle = Math.PI / 2 - 0.62
  controls.maxPolarAngle = Math.PI / 2 + 0.62
  controls.minAzimuthAngle = -Infinity
  controls.maxAzimuthAngle = Infinity

  controls.addEventListener('start', pauseAutoRotate)
  controls.addEventListener('end', resumeAutoRotate)

  createLights()
  resize()

  resizeObserver = new ResizeObserver(() => {
    resize()
  })
  resizeObserver.observe(stageRef.value)

  const loader = new GLTFLoader()

  loader.load(
    props.modelPath,
    (gltf) => {
      if (!scene) return

      modelRoot = gltf.scene
      modelRoot.rotation.y = -0.45
      modelRoot.rotation.x = 0.05

      modelRoot.traverse((child) => {
        const mesh = child as THREE.Mesh
        if (mesh.isMesh) {
          mesh.castShadow = false
          mesh.receiveShadow = false
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
    class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8"
    :style="{ '--phone-stage-height': height }"
  >
    <div class="grid gap-5 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)] lg:items-stretch">
      <div
        class="relative overflow-hidden rounded-[28px] border border-white/10 bg-black px-5 py-6 text-white shadow-[0_30px_80px_rgba(0,0,0,0.35)] sm:px-7 sm:py-8 xl:px-10 xl:py-10"
      >
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(255,255,255,0.08),transparent_34%),radial-gradient(circle_at_bottom_right,rgba(255,255,255,0.04),transparent_36%)]" />

        <div class="relative z-10">
          <div
            class="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/6 px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.28em] text-white/72 sm:text-xs"
          >
            3D Product View
          </div>

          <h2
            class="mt-5 max-w-[14ch] text-[32px] font-semibold leading-[0.92] tracking-[-0.05em] text-white sm:text-[44px] xl:text-[56px]"
          >
            Explore the phone in full 360°
          </h2>

          <p class="mt-5 max-w-[58ch] text-sm leading-7 text-white/72 sm:text-[15px] xl:text-base">
            Smooth automatic rotation, touch-friendly control, and premium product presentation.
            Touch or drag the phone to rotate it. When you stop, the automatic spin starts again.
          </p>

          <div class="mt-7 grid gap-3 sm:grid-cols-3">
            <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
              <div class="text-[11px] font-semibold uppercase tracking-[0.22em] text-white/46">
                Interaction
              </div>
              <div class="mt-2 text-sm font-medium text-white">Drag to rotate</div>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
              <div class="text-[11px] font-semibold uppercase tracking-[0.22em] text-white/46">
                Mobile
              </div>
              <div class="mt-2 text-sm font-medium text-white">Touch supported</div>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
              <div class="text-[11px] font-semibold uppercase tracking-[0.22em] text-white/46">
                Motion
              </div>
              <div class="mt-2 text-sm font-medium text-white">Auto rotates</div>
            </div>
          </div>

          <div class="mt-8 flex flex-wrap items-center gap-3">
            <div
              class="inline-flex items-center rounded-full border border-white/12 bg-white/[0.06] px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-white/76"
            >
              No zoom
            </div>
            <div
              class="inline-flex items-center rounded-full border border-white/12 bg-white/[0.06] px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-white/76"
            >
              Cursor + touch
            </div>
          </div>
        </div>
      </div>

      <div
        class="relative overflow-hidden rounded-[28px] border border-white/10 bg-black shadow-[0_30px_80px_rgba(0,0,0,0.35)]"
      >
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_50%_42%,rgba(255,255,255,0.09),transparent_24%),radial-gradient(circle_at_bottom,rgba(255,255,255,0.04),transparent_38%)]" />

        <div class="phone-3d__shell relative z-10">
          <div ref="stageRef" class="phone-3d__stage" />

          <div
            v-if="loading"
            class="pointer-events-none absolute inset-0 z-20 flex items-center justify-center"
          >
            <div class="rounded-full border border-white/14 bg-white/8 px-4 py-2 text-sm font-medium text-white">
              Loading 3D Model...
            </div>
          </div>

          <div
            v-if="error"
            class="absolute inset-0 z-30 flex items-center justify-center px-6 text-center"
          >
            <div class="rounded-2xl border border-red-300/25 bg-red-500/15 px-5 py-4 text-sm text-white">
              {{ error }}
            </div>
          </div>
        </div>

        <div
          class="pointer-events-none absolute bottom-4 left-1/2 z-20 -translate-x-1/2 rounded-full border border-white/12 bg-white/[0.06] px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-white/70"
        >
          Drag or touch the phone
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.phone-3d__shell {
  min-height: clamp(340px, 74vw, var(--phone-stage-height));
  padding: 18px;
}

.phone-3d__stage {
  position: relative;
  width: 100%;
  height: clamp(304px, 68vw, calc(var(--phone-stage-height) - 36px));
  max-width: 100%;
  border-radius: 24px;
  overflow: hidden;
  background:
    radial-gradient(circle at 50% 38%, rgba(255, 255, 255, 0.04), transparent 28%),
    #000;
}

.phone-3d__stage :deep(canvas) {
  display: block;
  width: 100% !important;
  height: 100% !important;
  outline: none;
  touch-action: none;
}

@media (min-width: 1024px) {
  .phone-3d__shell {
    min-height: var(--phone-stage-height);
    height: var(--phone-stage-height);
    padding: 24px;
  }

  .phone-3d__stage {
    height: 100%;
    max-width: 100%;
  }
}
</style>