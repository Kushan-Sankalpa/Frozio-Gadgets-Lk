<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const year = computed(() => new Date().getFullYear())
</script>

<template>
  <footer class="footer">
    <div class="footer-glow" />

    <div class="footer-container">
      <div class="footer-top">
        <div class="footer-brand">
          <Link :href="route('frontend.root')" class="brand-link" aria-label="FrozioHub Home">
            <img
              src="/assets/images/froziohub_new.png"
              alt="FrozioHub"
              class="footer-logo"
            />
          </Link>

          <p class="brand-text">
            Discover premium tech products and stylish footwear with a modern shopping experience designed for quality, simplicity, and trust.
          </p>
        </div>

        <div class="footer-grid">
          <div class="footer-column">
            <h3 class="footer-title">Quick Links</h3>

            <nav class="footer-links" aria-label="Quick links">
              <Link :href="route('frontend.root')" class="footer-link">Home</Link>
              <Link :href="route('frontend.tech-products.index')" class="footer-link">Tech Products</Link>
              <Link :href="route('frontend.shoe-products.index')" class="footer-link">Shoe Products</Link>
              <Link :href="route('frontend.contact-us.index')" class="footer-link">Contact Us</Link>
            </nav>
          </div>

          <div class="footer-column">
            <h3 class="footer-title">Shop</h3>

            <div class="footer-links">
              <Link
                :href="route('frontend.tech-products.index', { category: undefined, brand: undefined })"
                class="footer-link"
              >
                All Tech Collections
              </Link>

              <Link
                :href="route('frontend.shoe-products.index', { shoe_category: undefined, shoe_subcategory: undefined })"
                class="footer-link"
              >
                All Shoe Collections
              </Link>

              <Link :href="route('frontend.contact-us.index')" class="footer-link">
                Customer Assistance
              </Link>
            </div>
          </div>

          <div class="footer-column">
            <h3 class="footer-title">Contact Info</h3>

            <div class="contact-list">
              <div class="contact-item">
                <span class="contact-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M12 21s7-5.686 7-11a7 7 0 10-14 0c0 5.314 7 11 7 11z"
                    />
                    <circle cx="12" cy="10" r="2.5" />
                  </svg>
                </span>
                <p class="contact-text">522/c, Dharmapala Mawatha, Battaramulla</p>
              </div>

              <div class="contact-item">
                <span class="contact-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M22 16.92v3a2 2 0 01-2.18 2 19.86 19.86 0 01-8.63-3.07 19.5 19.5 0 01-6-6A19.86 19.86 0 012.08 4.18 2 2 0 014.06 2h3a2 2 0 012 1.72c.12.9.33 1.77.63 2.6a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.48-1.18a2 2 0 012.11-.45c.83.3 1.7.51 2.6.63A2 2 0 0122 16.92z"
                    />
                  </svg>
                </span>
                <div class="contact-text-group">
                  <a href="tel:0765807548" class="contact-link-text">0765807548</a>
                  <a href="tel:0773516451" class="contact-link-text">0773516451</a>
                </div>
              </div>

              <div class="contact-item">
                <span class="contact-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M4 6h16a1 1 0 011 1v10a1 1 0 01-1 1H4a1 1 0 01-1-1V7a1 1 0 011-1z"
                    />
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M22 7l-10 7L2 7"
                    />
                  </svg>
                </span>
                <a href="mailto:info@froziohub.com" class="contact-link-text">
                  info@froziohub.com
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="footer-bottom">
        <p class="footer-bottom-text">
          © {{ year }} FrozioHub. All rights reserved.
        </p>
      </div>
    </div>
  </footer>
</template>

<style scoped>
.footer {
  position: relative;
  overflow: hidden;
  width: 100%;
  background:
    radial-gradient(circle at top left, rgba(255, 255, 255, 0.08), transparent 28%),
    radial-gradient(circle at top right, rgba(255, 255, 255, 0.04), transparent 24%),
    #000000;
  color: #ffffff;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.footer-glow {
  position: absolute;
  inset: 0;
  pointer-events: none;
  background:
    linear-gradient(180deg, rgba(255, 255, 255, 0.03), transparent 18%),
    linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.02), transparent);
}

.footer-container {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 1280px;
  margin: 0 auto;
  padding: 64px 20px 22px;
}

.footer-top {
  display: grid;
  grid-template-columns: 1.15fr 1.85fr;
  gap: 48px;
  align-items: start;
}

.footer-brand {
  max-width: 460px;
}

.brand-link {
  display: inline-flex;
  align-items: center;
  text-decoration: none;
}

.footer-logo {
  width: auto;
  height: 110px;
  max-width: 340px;
  object-fit: contain;
}

.brand-text {
  margin: 20px 0 0;
  font-size: 0.98rem;
  line-height: 1.8;
  color: rgba(255, 255, 255, 0.72);
  max-width: 420px;
}

.footer-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 28px;
}

.footer-column {
  min-width: 0;
  padding: 6px 0;
}

.footer-title {
  margin: 0 0 18px;
  font-size: 0.95rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #ffffff;
}

.footer-links {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.footer-link {
  display: inline-flex;
  align-items: center;
  width: fit-content;
  font-size: 0.96rem;
  font-weight: 500;
  line-height: 1.5;
  color: rgba(255, 255, 255, 0.72);
  text-decoration: none;
  transition: color 0.22s ease, transform 0.22s ease;
}

.footer-link:hover {
  color: #ffffff;
  transform: translateX(3px);
}

.contact-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.contact-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.contact-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 42px;
  height: 42px;
  flex-shrink: 0;
  border-radius: 14px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.05);
  color: #ffffff;
}

.contact-icon svg {
  width: 18px;
  height: 18px;
}

.contact-text,
.contact-link-text {
  margin: 0;
  font-size: 0.95rem;
  line-height: 1.75;
  color: rgba(255, 255, 255, 0.74);
  text-decoration: none;
  transition: color 0.22s ease;
}

.contact-link-text:hover {
  color: #ffffff;
}

.contact-text-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.footer-bottom {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 18px;
  margin-top: 42px;
  padding-top: 22px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.footer-bottom-text {
  margin: 0;
  text-align: center;
  font-size: 0.88rem;
  line-height: 1.6;
  color: rgba(255, 255, 255, 0.58);
}

@media (max-width: 1080px) {
  .footer-top {
    grid-template-columns: 1fr;
    gap: 36px;
  }

  .footer-brand {
    max-width: none;
  }

  .footer-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .footer-logo {
    height: 100px;
    max-width: 320px;
  }
}

@media (max-width: 767px) {
  .footer-container {
    padding: 52px 16px 18px;
  }

  .footer-grid {
    grid-template-columns: 1fr;
    gap: 24px;
  }

  .footer-column {
    padding: 0;
  }

  .footer-title {
    margin-bottom: 14px;
  }

  .footer-logo {
    height: 88px;
    max-width: 280px;
  }

  .brand-text {
    font-size: 0.93rem;
    line-height: 1.75;
  }

  .footer-bottom {
    margin-top: 34px;
    padding-top: 18px;
  }
}

@media (max-width: 480px) {
  .footer-container {
    padding-left: 14px;
    padding-right: 14px;
  }

  .footer-logo {
    height: 76px;
    max-width: 240px;
  }

  .contact-item {
    gap: 10px;
  }

  .contact-icon {
    width: 38px;
    height: 38px;
    border-radius: 12px;
  }

  .contact-text,
  .contact-link-text {
    font-size: 0.91rem;
  }
}
</style>