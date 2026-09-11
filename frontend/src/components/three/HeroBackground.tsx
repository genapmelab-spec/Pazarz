import { useRef, useMemo } from 'react'
import { Canvas, useFrame } from '@react-three/fiber'
import { Float, MeshDistortMaterial } from '@react-three/drei'
import * as THREE from 'three'

function FloatingParticle({ position, size, speed }: { position: [number, number, number]; size: number; speed: number }) {
  const ref = useRef<THREE.Mesh>(null)

  useFrame((state) => {
    if (!ref.current) return
    ref.current.position.y = position[1] + Math.sin(state.clock.elapsedTime * speed) * 0.3
    ref.current.position.x = position[0] + Math.cos(state.clock.elapsedTime * speed * 0.5) * 0.1
  })

  return (
    <mesh ref={ref} position={position}>
      <sphereGeometry args={[size, 16, 16]} />
      <meshStandardMaterial
        color="#ffffff"
        transparent
        opacity={0.15}
        roughness={0.5}
        metalness={0.3}
      />
    </mesh>
  )
}

function FloatingGeometry() {
  const meshRef = useRef<THREE.Mesh>(null)

  useFrame((state) => {
    if (!meshRef.current) return
    meshRef.current.rotation.x = state.clock.elapsedTime * 0.1
    meshRef.current.rotation.y = state.clock.elapsedTime * 0.15
  })

  return (
    <Float speed={1.5} rotationIntensity={0.5} floatIntensity={1.2}>
      <mesh ref={meshRef} position={[0, 0, 0]}>
        <icosahedronGeometry args={[1.5, 1]} />
        <MeshDistortMaterial
          color="#ffffff"
          transparent
          opacity={0.08}
          distort={0.3}
          speed={2}
          roughness={0.2}
          metalness={0.8}
          wireframe
        />
      </mesh>
    </Float>
  )
}

function FloatingRing({ position, scale }: { position: [number, number, number]; scale: number }) {
  const ref = useRef<THREE.Mesh>(null)

  useFrame((state) => {
    if (!ref.current) return
    ref.current.rotation.x = state.clock.elapsedTime * 0.2
    ref.current.rotation.z = state.clock.elapsedTime * 0.1
  })

  return (
    <mesh ref={ref} position={position} scale={scale}>
      <torusGeometry args={[1, 0.02, 16, 64]} />
      <meshStandardMaterial
        color="#ffffff"
        transparent
        opacity={0.06}
        roughness={0.3}
        metalness={0.9}
      />
    </mesh>
  )
}

function Particles() {
  const particles = useMemo(() => {
    return Array.from({ length: 40 }, (_, i) => ({
      position: [
        (Math.random() - 0.5) * 10,
        (Math.random() - 0.5) * 6,
        (Math.random() - 0.5) * 4 - 2,
      ] as [number, number, number],
      size: Math.random() * 0.04 + 0.01,
      speed: Math.random() * 0.5 + 0.3,
    }))
  }, [])

  return (
    <>
      {particles.map((p, i) => (
        <FloatingParticle key={i} {...p} />
      ))}
    </>
  )
}

export function HeroBackground() {
  return (
    <div className="absolute inset-0 z-0">
      <Canvas
        camera={{ position: [0, 0, 5], fov: 45 }}
        dpr={[1, 1.5]}
        gl={{ antialias: true, alpha: true }}
        style={{ background: 'transparent' }}
      >
        <ambientLight intensity={0.6} />
        <directionalLight position={[5, 5, 5]} intensity={0.4} />
        <pointLight position={[-5, -5, 5]} intensity={0.2} color="#4B6FFF" />

        <Particles />
        <FloatingGeometry />
        <FloatingRing position={[3, 1, -2]} scale={0.8} />
        <FloatingRing position={[-2.5, -0.5, -3]} scale={0.6} />
      </Canvas>
    </div>
  )
}
