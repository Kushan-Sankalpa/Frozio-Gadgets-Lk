<template>
  <footer class="footer">
    <div class="footer-container">
      <!-- Footer Top Section -->
      <div class="footer-top">
        <!-- Brand Section -->
        <div class="footer-brand">
      <Link :href="route('frontend.root')" class="footer-logo">

          <img src="/images/icons/main-logo.png" alt="Nikoba Logo">
          </Link>
          <p>In the year 2003 Nikoba.com revved up the corporate engines with a mission to be the dominant force in the
            automobile industry in Sri Lanka.</p>

          <!-- App Download Section -->
          <div class="app-download">
            <a href="https://apps.apple.com/lk/app/nikoba-car-auction/id6717583193" target="_blank" class="app-button">
              <div class="app-icon">
                <i class="fab fa-apple"></i>
              </div>
              <div class="app-text">
                <span class="app-store-text">Download on the</span>
                <span class="app-store-name">Apple Store</span>
              </div>
            </a>
            <a href="https://play.google.com/store/apps/details?id=com.nikoba.app&pcampaignid=web_share&pli=1"
              target="_blank" class="app-button">
              <div class="app-icon">
                <i class="fab fa-google-play"></i>
              </div>
              <div class="app-text">
                <span class="app-store-text">Get it on</span>
                <span class="app-store-name">Google Play</span>
              </div>
            </a>
          </div>

          <!-- QR Codes -->
          <div class="qr-codes">
            <img src="/images/home/Apple1.png" alt="QR Code Apple" class="qr-code">
            <img src="/images/home/Playstore2.png" alt="QR Code Google" class="qr-code">
          </div>
        </div>

        <!-- Marquee Section (replacing Newsletter) -->
        <div class="footer-marquee">
          <div class="marquee-wrapper">
            <div class="marquee-content" id="marqueeText">
              NIKOBA.NIKOBA.NIKOBA.NIKOBA.NIKOBA.NIKOBA.NIKOBA.NIKOBA
            </div>
          </div>
        </div>
      </div>

      <!-- Footer Links Section -->
      <div class="footer-links">
        <!-- Menu Column -->
        <div class="footer-links-column">
          <h4 class="footer-links-title">Menu</h4>
          <ul class="footer-links-list">
            <li><a :href="route('about')">About Us</a></li>
            <li><a :href="route('live.auction')">Live Auction in Japan</a></li>
            <li><a :href="route('service')">Services</a></li>
            <li><a :href="route('how.to.order')">How To Order</a></li>
            <li><a :href="route('contact')">Contact Us</a></li>
          </ul>
        </div>

        <!-- Quick Links Column -->
        <div class="footer-links-column">
          <h4 class="footer-links-title">Quick Links</h4>
          <ul class="footer-links-list">
            <li><a :href="route('faq')">FAQ</a></li>
            <li><a :href="route('live.auction')">Live Auction in Japan</a></li>
            <li><a :href="route('testimonials')">Testimonials</a></li>
            <li><a :href="route('service')">Services</a></li>
            <li><a :href="route('how.to.order')">How To Order</a></li>
          </ul>
        </div>

        <!-- Our Brands Column -->
        <div class="footer-links-column">
          <h4 class="footer-links-title">Our Brands</h4>
          <ul class="footer-links-list">
            <li v-for="(manufacture, index) in $page.props.category_manufactures.slice(0, 8)" :key="index">
              <Link method="post" :href="route('live.auction')"
                :data="{ _method: 'GET', manufacturer: manufacture.name, search: true }" class="brand-link">
              {{ manufacture.name }}
              </Link>
            </li>
          </ul>
        </div>

        <!-- Vehicles Type Column -->

        <div class="footer-links-column">
          <h4 class="footer-links-title">Vehicles Type</h4>
          <ul class="footer-links-list">
            <li v-for="(type, index) in $page.props.vehicle_types" :key="index">
              <Link method="post" :href="route('available')" :data="{ _method: 'GET', type: type.id }">
              {{ type.title }}
              </Link>
            </li>
          </ul>
        </div>

        <!-- Mobile Numbers Column -->
        <div class="footer-links-column footer-mobile">
          <h4 class="footer-links-title">Our Mobile Numbers</h4>
          <div class="mobile-numbers">
            <a href="tel:815050508000" class="mobile-number">
              <i class="fas fa-phone mobile-icon"></i>
              + 81 50 50 50 8000
            </a>
            <a href="tel:0112111777" class="mobile-number">
              <i class="fas fa-phone mobile-icon"></i>
              0112 111 777
            </a>
          </div>

          <!-- Social Media Section -->
          <div class="footer-social">
            <h4 class="footer-social-title">Connect With Us</h4>
            <div class="social-icons">
              <a href="https://www.facebook.com/Nikoba-227922574046015/?ref=ts" target="_blank" class="social-icon">
                <i class="fab fa-facebook-f"></i>
              </a>
              <a href="https://twitter.com/Nikoba1" target="_blank" class="social-icon">
                <i class="fab fa-twitter"></i>
              </a>
              <!-- <a href="#" class="social-icon">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-icon">
                            <i class="fab fa-linkedin-in"></i>
                        </a> -->
            </div>
          </div>
        </div>
      </div>

      <!-- Footer Bottom Section -->
      <div class="footer-bottom">
  <div class="footer-copyright">
    © 2025 nikoba.com. All rights reserved.
  </div>
  <div class="footer-legal">
    <a :href="route('privacy.policy.dealer')">Terms & Conditions</a>
    <span>•</span>
    <a :href="route('privacy.policy')">Privacy Notice</a>
  </div>
  <div class="developer-credit">
    <span>Web Design & Develop </span>
    <a href="https://weblook.com/" target="_blank" rel="noopener" class="developer-logo-link">
      <img src="/images/web.png" alt="weblook.com" class="developer-logo" />
    </a>
  </div>
</div>
    </div>

  </footer>
</template>

<script>
import { Link } from '@inertiajs/vue3';


export default {
  components: {
    Link
  },
  props: {
    vehicle_types: Object,
    category_manufactures: Object
  },
  mounted() {
    this.initMarquee();
  },
  methods: {
    initMarquee() {
      const marquee = document.getElementById('marqueeText');
      if (marquee) {
        const text = marquee.textContent;
        marquee.textContent = text + ' ' + text; // Duplicate text for seamless loop
      }
    }
  }
}
</script>

<style scoped>
/* Footer styles */
.developer-credit {
  display: flex;
  align-items: center;
  gap: 5px;
  color: #aaa;
  font-size: 12px;
}

.developer-logo-link {
  display: inline-block;
}

.developer-logo {
  height: 20px;
  width: auto;
}
.footer {
  background-color: #0a1026;
  color: #ffffff;
  padding: 2em;
  width: 100%;
}

.footer-container {
  max-width: 95%;
  margin: 0 auto;
  padding: 0 20px;
}

.footer-top {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  margin-bottom: 40px;
}

/* Brand section */
.footer-brand {
  flex: 1;
  min-width: 250px;
  margin-bottom: 30px;
}

.footer-logo {
  display: inline-block;
  margin-bottom: 15px;
}

.footer-logo img {
  height: 4rem;
  width: auto;
}

.footer-brand p {
  color: #ccc;
  font-size: 14px;
  line-height: 1.6;
  margin-bottom: 20px;
  max-width: 350px;
}

/* App download section */
.app-download {
  display: flex;
  gap: 15px;
  margin-bottom: 20px;
}

.app-button {
  display: flex;
  align-items: center;
  background-color: #1e293b;
  border-radius: 8px;
  padding: 8px 12px;
  text-decoration: none;
  color: white;
  font-size: 12px;
  transition: all 0.3s ease;
}

.app-button:hover {
  background-color: #2d3a4f;
}

.app-icon {
  margin-right: 8px;
  font-size: 20px;
}

.app-text {
  display: flex;
  flex-direction: column;
}

.app-store-text {
  font-size: 10px;
  opacity: 0.8;
}

.app-store-name {
  font-weight: bold;
}

/* QR codes section */
.qr-codes {
  display: flex;
  gap: 20px;
}

.qr-code {
  width: 120px;
  height: 120px;
  background-color: white;
  padding: 5px;
  border-radius: 4px;
}

/* Marquee section */
.footer-marquee {
  padding-top: 5rem;
  flex: 1;
  min-width: 65%;
  margin-bottom: 10px;
  overflow: hidden;
}

.marquee-wrapper {
  height: 40vh;
  overflow: hidden;
  position: relative;
  width: 100%;
}

.marquee-content {
  position: absolute;
  white-space: nowrap;
  font-size: 12em;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 2px;
  -webkit-text-stroke: 1.5px rgba(255, 255, 255, 0.8);
  color: transparent;
  will-change: transform;
  animation: marquee 20s linear infinite;
}

@keyframes marquee {
  0% {
    transform: translateX(0);
  }

  100% {
    transform: translateX(-20%);
  }
}

/* Footer links section */
.footer-links {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  margin-bottom: 30px;
}

.footer-links-column {
  flex: 1;
  min-width: 150px;
  margin-bottom: 25px;
  padding-right: 15px;
}

.footer-links-title {
  font-size: 16px;
  font-weight: bold;
  margin-bottom: 15px;
  color: #ffffff;
}

.footer-links-list {
  list-style: none;
  padding: 0;
}

.footer-links-list li {
  margin-bottom: 10px;
}

.footer-links-list a {
  color: #ccc;
  text-decoration: none;
  font-size: 14px;
  transition: color 0.3s ease;
}

.footer-links-list a:hover {
  color: #ffffff;
}

/* Mobile numbers section */
.footer-mobile {
  flex: 1;
  min-width: 200px;
}

.mobile-numbers {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.mobile-number {
  display: flex;
  align-items: center;
  background-color: transparent;
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 100px;
  padding: 10px 15px;
  color: white;
  text-decoration: none;
  transition: background-color 0.3s ease;
}

.mobile-number:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.mobile-icon {
  margin-right: 8px;
}

/* Social media section */
.footer-social {
  margin-top: 20px;
}

.footer-social-title {
  font-size: 16px;
  font-weight: bold;
  margin-bottom: 15px;
  color: #ffffff;
}

.social-icons {
  display: flex;
  gap: 15px;
}

.social-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background-color: rgba(255, 255, 255, 0.1);
  color: white;
  text-decoration: none;
  transition: background-color 0.3s ease;
}

.social-icon:hover {
  background-color: rgba(255, 255, 255, 0.2);
}

/* Footer bottom */
.footer-bottom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  font-size: 12px;
  color: #aaa;
  flex-wrap: wrap;
}

@media (max-width: 768px) {
  .footer-bottom {
    flex-direction: column;
    text-align: center;
    gap: 15px;
  }

  .footer-copyright {
    margin-right: 0;
  }

  .footer-legal {
    justify-content: center;
  }
}
@media (max-width: 768px) {
  /* Existing styles */
  .footer-bottom {
    flex-direction: column;
    text-align: center;
    gap: 15px;
  }

  .footer-copyright {
    margin-right: 0;
  }

  .footer-legal {
    justify-content: center;
  }

  .developer-credit {
    justify-content: center;
  }
}
.footer-copyright {
  margin-right: 20px;
}

.footer-legal {
  display: flex;
  gap: 15px;
}

.footer-legal a {
  color: #aaa;
  text-decoration: none;
  transition: color 0.3s ease;
}

.footer-legal a:hover {
  color: #ffffff;
}

/* Responsive design */
@media (max-width: 768px) {
  .footer-top {
    flex-direction: column;
  }

  .footer-links {
    flex-wrap: wrap;
  }

  .footer-links-column {
    min-width: 45%;
  }

  .footer-bottom {
    flex-direction: column;
    text-align: center;
    gap: 15px;
  }

  .footer-copyright {
    margin-right: 0;
  }

  .footer-legal {
    justify-content: center;
  }

  .marquee-content {
    font-size: 28px;
  }

  .marquee-wrapper {
    height: 70px;
  }
}

@media (max-width: 480px) {
  .app-download {
    flex-direction: column;
    align-items: flex-start;
  }

  .footer-links-column {
    min-width: 100%;
  }

  .qr-codes {
    flex-direction: column;
  }

  .marquee-content {
    font-size: 22px;
  }

  .marquee-wrapper {
    height: 60px;
  }
}
</style>
