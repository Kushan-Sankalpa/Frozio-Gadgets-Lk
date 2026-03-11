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
    background?: string
  }>(),
  {
    modelPath: '/models/iphone_17_pro_max.glb',
    height: '620px',
    autoRotate: false,
    background: 'transparent',
  },
)

const containerRef = ref<HTMLElement | null>(null)
const loading = ref(true)
const error = ref('')

let scene: THREE.Scene | null = null
let camera: THREE.PerspectiveCamera | null = null
let renderer: THREE.WebGLRenderer | null = null
let controls: OrbitControls | null = null
let modelRoot: THREE.Group | null = null
let frameId = 0
let resizeObserver: ResizeObserver | null = null

const fitCameraToObject = (object: THREE.Object3D) => {
  if (!camera || !controls) {
    return
  }

  const box = new THREE.Box3().setFromObject(object)
  const size = box.getSize(new THREE.Vector3())
  const center = box.getCenter(new THREE.Vector3())

  object.position.sub(center)

  const maxDim = Math.max(size.x, size.y, size.z)
  const fov = (camera.fov * Math.PI) / 180
  const cameraZ = Math.abs(maxDim / 2 / Math.tan(fov / 2)) * 1.6

  camera.position.set(cameraZ * 0.25, cameraZ * 0.08, cameraZ)
  camera.near = Math.max(0.01, maxDim / 100)
  camera.far = Math.max(1000, maxDim * 20)
  camera.updateProjectionMatrix()

  controls.target.set(0, 0, 0)
  controls.minDistance = maxDim * 0.8
  controls.maxDistance = maxDim * 4
  controls.update()
}

const createLights = () => {
  if (!scene) {
    return
  }

  const ambient = new THREE.AmbientLight(0xffffff, 1.4)
  scene.add(ambient)

  const hemi = new THREE.HemisphereLight(0xffffff, 0x1e293b, 1.2)
  hemi.position.set(0, 20, 0)
  scene.add(hemi)

  const key = new THREE.DirectionalLight(0xffffff, 2.2)
  key.position.set(5, 8, 10)
  scene.add(key)

  const fill = new THREE.DirectionalLight(0xffffff, 1.1)
  fill.position.set(-6, 3, 6)
  scene.add(fill)

  const rim = new THREE.DirectionalLight(0xffffff, 1.4)
  rim.position.set(0, 6, -10)
  scene.add(rim)
}

const resize = () => {
  if (!containerRef.value || !camera || !renderer) {
    return
  }

  const width = containerRef.value.clientWidth
  const height = containerRef.value.clientHeight

  if (!width || !height) {
    return
  }

  camera.aspect = width / height
  camera.updateProjectionMatrix()
  renderer.setSize(width, height)
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2))
}

const animate = () => {
  if (!renderer || !scene || !camera) {
    return
  }

  frameId = window.requestAnimationFrame(animate)

  controls?.update()
  renderer.render(scene, camera)
}

const disposeScene = () => {
  if (frameId) {
    window.cancelAnimationFrame(frameId)
    frameId = 0
  }

  controls?.dispose()
  controls = null

  if (modelRoot) {
    modelRoot.traverse((child) => {
      const mesh = child as THREE.Mesh

      if (mesh.geometry) {
        mesh.geometry.dispose()
      }

      const material = mesh.material
      if (Array.isArray(material)) {
        material.forEach((m) => m.dispose())
      } else if (material) {
        material.dispose()
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

  if (containerRef.value) {
    containerRef.value.innerHTML = ''
  }

  resizeObserver?.disconnect()
  resizeObserver = null
}

onMounted(() => {
  if (!containerRef.value) {
    return
  }

  scene = new THREE.Scene()

  camera = new THREE.PerspectiveCamera(35, 1, 0.1, 1000)
  camera.position.set(0, 0.2, 4)

  renderer = new THREE.WebGLRenderer({
    antialias: true,
    alpha: props.background === 'transparent',
    powerPreference: 'high-performance',
  })

  renderer.outputColorSpace = THREE.SRGBColorSpace
  renderer.toneMapping = THREE.ACESFilmicToneMapping
  renderer.toneMappingExposure = 1.05
  renderer.shadowMap.enabled = false

  if (props.background !== 'transparent') {
    renderer.setClearColor(props.background)
  } else {
    renderer.setClearAlpha(0)
  }

  containerRef.value.appendChild(renderer.domElement)

  controls = new OrbitControls(camera, renderer.domElement)
  controls.enableDamping = true
  controls.dampingFactor = 0.08
  controls.rotateSpeed = 0.85
  controls.zoomSpeed = 0.9
  controls.enablePan = false
  controls.autoRotate = props.autoRotate
  controls.autoRotateSpeed = 1.2
  controls.minPolarAngle = 0
  controls.maxPolarAngle = Math.PI

  createLights()
  resize()

  resizeObserver = new ResizeObserver(() => {
    resize()
  })
  resizeObserver.observe(containerRef.value)

  const loader = new GLTFLoader()

  loader.load(
    props.modelPath,
    (gltf) => {
      if (!scene) {
        return
      }

      modelRoot = gltf.scene

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
  <div
    class="phone-3d relative w-full overflow-hidden rounded-[28px]"
    :style="{ height, background: background === 'transparent' ? 'transparent' : background }"
  >
    <div ref="containerRef" class="absolute inset-0 h-full w-full" />

    <div
      v-if="loading"
      class="absolute inset-0 z-10 flex items-center justify-center bg-black/10 backdrop-blur-[2px]"
    >
      <div class="rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm font-medium text-white">
        Loading 3D Model...
      </div>
    </div>

    <div
      v-if="error"
      class="absolute inset-0 z-20 flex items-center justify-center bg-black/20 px-6 text-center"
    >
      <div class="rounded-2xl border border-red-300/30 bg-red-500/15 px-5 py-4 text-sm text-white">
        {{ error }}
      </div>
    </div>
  </div>
</template>

<style scoped>
.phone-3d {
  min-height: 320px;
}

.phone-3d :deep(canvas) {
  display: block;
  width: 100% !important;
  height: 100% !important;
  touch-action: none;
  outline: none;
}
</style>