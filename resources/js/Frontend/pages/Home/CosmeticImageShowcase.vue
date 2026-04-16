<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
</script>

<template>
  <section class="relative overflow-hidden bg-white">
    <div class="pointer-events-none absolute inset-0">
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_left_top,rgba(236,72,153,0.10),transparent_36%)]" />
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_right_center,rgba(99,102,241,0.08),transparent_34%)]" />
      <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(15,23,42,0.02),transparent_18%,transparent_82%,rgba(15,23,42,0.02))]" />
    </div>

    <div class="relative mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8 lg:py-16">
      <div class="grid items-center gap-8 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)] lg:gap-12 xl:gap-16">
        <div class="order-2 lg:order-1">
          <div class="inline-flex w-fit items-center rounded-full border border-slate-200 bg-white px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500 shadow-sm sm:text-xs">
            Skin Essential
          </div>

          <h2 class="mt-5 max-w-[18ch] text-[40px] font-semibold leading-[0.95] tracking-[-0.05em] text-slate-900 sm:text-[56px] lg:text-[64px] xl:text-[76px]">
            The Ordinary Niacinamide
          </h2>

          <p class="mt-5 max-w-[64ch] text-[15px] leading-7 text-slate-600 sm:text-base sm:leading-8">
            A simple everyday serum loved for a reason — a lightweight formula that helps visibly reduce the look of
            blemishes, refine pores, and support a smoother-looking complexion.
          </p>

          <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:items-center">
            <Link
              :href="route('frontend.cosmetic-products.index')"
              class="inline-flex min-h-[52px] items-center justify-center rounded-full bg-slate-950 px-7 text-sm font-semibold text-white transition hover:bg-slate-800"
            >
              Shop Cosmetics
            </Link>

            <span class="text-sm font-medium text-slate-500">
              Brand, category, country filters available
            </span>
          </div>
        </div>

        <div class="order-1 lg:order-2">
          <div class="relative ml-auto w-full max-w-[760px]">
            <div class="relative overflow-hidden rounded-[28px] border border-slate-200 bg-gradient-to-br from-slate-950 to-slate-800 shadow-[0_26px_70px_rgba(15,23,42,0.18)]">
              <div class="absolute inset-0 opacity-90 bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.14),transparent_45%)]" />

              <div class="relative grid place-items-center p-10 sm:p-14">
                <svg
                  viewBox="0 0 340 420"
                  class="w-full max-w-[320px] drop-shadow-[0_26px_34px_rgba(0,0,0,0.28)]"
                  aria-hidden="true"
                >
                  <defs>
                    <linearGradient id="glass" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="0" stop-color="#f8fafc" stop-opacity="0.92" />
                      <stop offset="1" stop-color="#e2e8f0" stop-opacity="0.72" />
                    </linearGradient>
                    <linearGradient id="cap" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="0" stop-color="#0f172a" />
                      <stop offset="1" stop-color="#111827" />
                    </linearGradient>
                  </defs>

                  <rect x="120" y="18" width="100" height="70" rx="18" fill="url(#cap)" />
                  <rect x="106" y="78" width="128" height="28" rx="14" fill="#111827" opacity="0.9" />

                  <rect x="78" y="96" width="184" height="282" rx="54" fill="url(#glass)" />
                  <rect x="92" y="110" width="156" height="254" rx="44" fill="#ffffff" opacity="0.5" />

                  <rect x="104" y="162" width="132" height="120" rx="22" fill="#ffffff" opacity="0.92" />
                  <text x="170" y="206" text-anchor="middle" font-size="16" font-weight="700" fill="#0f172a" font-family="system-ui, -apple-system, Segoe UI, Roboto">
                    The Ordinary
                  </text>
                  <text x="170" y="236" text-anchor="middle" font-size="13" font-weight="600" fill="#334155" font-family="system-ui, -apple-system, Segoe UI, Roboto">
                    Niacinamide 10%
                  </text>
                  <text x="170" y="258" text-anchor="middle" font-size="12" font-weight="600" fill="#64748b" font-family="system-ui, -apple-system, Segoe UI, Roboto">
                    + Zinc 1%
                  </text>

                  <circle cx="116" cy="142" r="40" fill="#fff" opacity="0.12" />
                </svg>

                <div class="mt-8 grid w-full max-w-[420px] grid-cols-3 gap-3 text-center text-xs font-semibold text-white/80">
                  <div class="rounded-2xl border border-white/10 bg-white/5 px-3 py-3">
                    Brightening
                  </div>
                  <div class="rounded-2xl border border-white/10 bg-white/5 px-3 py-3">
                    Oil Control
                  </div>
                  <div class="rounded-2xl border border-white/10 bg-white/5 px-3 py-3">
                    Pore Care
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

