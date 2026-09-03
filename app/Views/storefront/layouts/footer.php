<!-- Main Storefront Footer -->
<footer class="bg-white text-xs border-t border-gray-900 mt-auto font-sans text-gray-600">

    <!-- ========================================================= -->
    <!-- MOBILE FOOTER — Accordion & Clean Layout (block below md) -->
    <!-- ========================================================= -->
    <div class="md:hidden">

        <!-- Company Info & Newsletter Mobile -->
        <div class="bg-white border-b border-gray-100 px-4 py-6 space-y-4">
            <img src="<?= asset('images/importwale-logo.png') ?>" alt="ImportWale Logo"
                class="h-12 max-w-[180px] object-contain"
                onerror="this.onerror=null; this.src='https://via.placeholder.com/160x48?text=IMPORTWALE';">
            <p class="text-xs text-gray-500 font-normal leading-relaxed">
                India's premier manufacturer of high-grade electric scooter accessories, crash guards, and protective
                equipment. Engineered for maximum safety &amp; style.
            </p>
            <form action="#" method="POST" @submit.prevent="alert('Thank you for subscribing to ImportWale updates!')"
                class="space-y-2 pt-1">
                <p class="text-xs font-semibold text-gray-900">Subscribe for Exclusive Deals</p>
                <div class="flex gap-2">
                    <input type="email" required placeholder="Enter Email Address"
                        class="w-full h-10 px-3.5 bg-gray-50 border border-gray-900 rounded-xl text-xs font-medium text-gray-900 placeholder-gray-400 focus:outline-none focus:border-red-600 transition">
                    <button type="submit"
                        class="h-10 px-4 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl transition shrink-0">
                        Subscribe
                    </button>
                </div>
            </form>
        </div>

        <!-- Accordion Sections -->
        <div class="divide-y divide-gray-100 bg-white" x-data="{ openSection: null }">

            <!-- Quick Links -->
            <div>
                <button @click="openSection = openSection === 'links' ? null : 'links'"
                    class="w-full flex items-center justify-between px-4 py-4 text-left font-semibold text-gray-900 text-sm">
                    <span>Quick Links</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 transition-transform duration-200"
                        :class="openSection === 'links' ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openSection === 'links'" x-collapse class="px-4 pb-4 space-y-2.5">
                    <a href="<?= url('/') ?>" class="block text-gray-600 hover:text-red-600 font-medium">Home</a>
                    <a href="<?= url('shop') ?>" class="block text-gray-600 hover:text-red-600 font-medium">Shop All
                        Accessories</a>
                    <a href="<?= url('brands') ?>" class="block text-gray-600 hover:text-red-600 font-medium">Scooter
                        Brands</a>
                    <a href="<?= url('categories') ?>"
                        class="block text-gray-600 hover:text-red-600 font-medium">Categories</a>
                    <a href="<?= url('about-us') ?>" class="block text-gray-600 hover:text-red-600 font-medium">About
                        ImportWale</a>
                    <a href="<?= url('contact-us') ?>"
                        class="block text-gray-600 hover:text-red-600 font-medium">Contact Us</a>
                </div>
            </div>

            <!-- Customer Care & Policies -->
            <div>
                <button @click="openSection = openSection === 'pol' ? null : 'pol'"
                    class="w-full flex items-center justify-between px-4 py-4 text-left font-semibold text-gray-900 text-sm">
                    <span>Policies &amp; Legal</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 transition-transform duration-200"
                        :class="openSection === 'pol' ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openSection === 'pol'" x-collapse class="px-4 pb-4 space-y-2.5">
                    <a href="<?= url('support') ?>" class="block text-gray-600 hover:text-red-600 font-medium">Help
                        Center &amp; FAQs</a>
                    <a href="<?= url('shipping-policy') ?>"
                        class="block text-gray-600 hover:text-red-600 font-medium">Shipping &amp; Delivery Policy</a>
                    <a href="<?= url('refund-policy') ?>"
                        class="block text-gray-600 hover:text-red-600 font-medium">Returns &amp; Replacement Policy</a>
                    <a href="<?= url('cancellation-policy') ?>"
                        class="block text-gray-600 hover:text-red-600 font-medium">Cancellation Policy</a>
                    <a href="<?= url('privacy-policy') ?>"
                        class="block text-gray-600 hover:text-red-600 font-medium">Privacy Policy</a>
                    <a href="<?= url('terms-and-conditions') ?>"
                        class="block text-gray-600 hover:text-red-600 font-medium">Terms &amp; Conditions</a>
                </div>
            </div>

            <!-- My Account -->
            <div>
                <button @click="openSection = openSection === 'account' ? null : 'account'"
                    class="w-full flex items-center justify-between px-4 py-4 text-left font-semibold text-gray-900 text-sm">
                    <span>Customer Portal</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 transition-transform duration-200"
                        :class="openSection === 'account' ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openSection === 'account'" x-collapse class="px-4 pb-4 space-y-2.5">
                    <a href="<?= url('account') ?>" class="block text-gray-600 hover:text-red-600 font-medium">My
                        Account</a>
                    <a href="<?= url('wishlist') ?>"
                        class="block text-gray-600 hover:text-red-600 font-medium">Wishlist</a>
                    <a href="<?= url('compare') ?>" class="block text-gray-600 hover:text-red-600 font-medium">Compare
                        Products</a>
                    <a href="<?= url('cart') ?>" class="block text-gray-600 hover:text-red-600 font-medium">Shopping
                        Cart</a>
                    <a href="https://wa.me/919217714452?text=Hi%20ImportWale%2C%20I%20need%20help%20with%20my%20order."
                        target="_blank" rel="noopener noreferrer"
                        class="flex items-center space-x-1.5 text-emerald-600 hover:text-emerald-700 font-medium">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                        </svg>
                        <span>WhatsApp: 92177 14452</span>
                    </a>
                </div>
            </div>

        </div>



        <!-- Mobile Copyright & Payment Icons -->
        <div class="bg-gray-900 px-4 pt-5 pb-20 text-center space-y-3">
            <p class="text-[10px] text-gray-400 leading-relaxed">
                &copy; <?= date('Y') ?> ImportWale (Rughwani Enterprises) &middot; GSTIN: 07FLOPR6641L1Z8 &middot; All
                Rights Reserved.
            </p>
            <div class="flex items-center justify-center flex-wrap gap-1.5">
                <span class="bg-white/10 text-white font-semibold text-[9px] px-2 py-0.5 rounded">UPI</span>
                <span class="bg-white/10 text-white font-semibold text-[9px] px-2 py-0.5 rounded">Razorpay</span>
                <span class="bg-white/10 text-white font-semibold text-[9px] px-2 py-0.5 rounded">Visa</span>
                <span class="bg-white/10 text-white font-semibold text-[9px] px-2 py-0.5 rounded">Mastercard</span>
                <span class="bg-white/10 text-white font-semibold text-[9px] px-2 py-0.5 rounded">COD</span>
            </div>
        </div>

    </div><!-- /mobile footer -->

    <!-- ========================================================= -->
    <!-- DESKTOP FOOTER — Professional E-Commerce Brand Footer     -->
    <!-- ========================================================= -->
    <div class="hidden md:block">

        <!-- Newsletter Bar -->
        <div class="bg-gray-100 py-8 border-b border-gray-900">
            <div class="container mx-auto px-4 flex flex-col lg:flex-row items-center justify-between gap-6">
                <div class="flex items-center space-x-3.5">
                    <div
                        class="w-12 h-12 rounded-2xl bg-red-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                        <i data-lucide="mail-open" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 leading-tight">
                            Subscribe for Fitment &amp; Discount Alerts
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5 font-medium">
                            Get weekly updates on new EV accessory releases and subscriber-only coupons.
                        </p>
                    </div>
                </div>

                <form action="#" method="POST" @submit.prevent="alert('Thank you for subscribing to ImportWale updates!')"
                    class="flex items-center gap-2 w-full lg:w-auto">
                    <input type="text" required placeholder="First Name"
                        class="h-11 px-4 bg-white border border-gray-300 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:border-red-600 transition">
                    <input type="email" required placeholder="Email Address"
                        class="h-11 px-4 bg-white border border-gray-300 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:border-red-600 transition min-w-[220px]">
                    <button type="submit"
                        class="h-11 px-6 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl transition shadow-xs whitespace-nowrap">
                        Subscribe →
                    </button>
                </form>
            </div>
        </div>

        <!-- 4-Column Main Link Grid -->
        <div class="container mx-auto px-4 py-12 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-8">

            <!-- Column 1: Company About (Span 4) -->
            <div class="lg:col-span-4 space-y-4">
                <div class="flex items-center space-x-2">
                    <img src="<?= asset('images/importwale-logo.png') ?>" alt="ImportWale Logo"
                        class="h-14 md:h-16 max-w-[240px] object-contain"
                        onerror="this.onerror=null; this.src='https://via.placeholder.com/220x60?text=IMPORTWALE';">
                </div>
                <p class="text-xs text-gray-500 font-normal leading-relaxed pr-4">
                    ImportWale is India's leading brand for heavy-duty electric scooter accessories, crash guards, and
                    protective equipment. Engineered for maximum safety, precision fitment, and long-lasting durability
                    across Ola, Ather, TVS, Honda, Bajaj, and Hero EV models.
                </p>
                <div class="space-y-2 text-xs font-medium text-gray-700 pt-1">
                    <div class="flex items-center space-x-2">
                        <i data-lucide="mail" class="w-4 h-4 text-red-600 shrink-0"></i>
                        <span>support@importwale.com</span>
                    </div>
                    <a href="tel:+919217714452" class="flex items-center space-x-2 hover:text-red-600 transition">
                        <i data-lucide="phone" class="w-4 h-4 text-red-600 shrink-0"></i>
                        <span>+91 92177 14452 (Mon - Sat, 10 AM - 7 PM)</span>
                    </a>
                    <a href="https://wa.me/919217714452?text=Hi%20ImportWale%2C%20I%20need%20help%20with%20my%20order."
                        target="_blank" rel="noopener noreferrer"
                        class="flex items-center space-x-2 text-emerald-600 hover:text-emerald-700 transition">
                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                        </svg>
                        <span>WhatsApp: 92177 14452</span>
                    </a>
                    <div class="flex items-center space-x-2">
                        <i data-lucide="map-pin" class="w-4 h-4 text-red-600 shrink-0"></i>
                        <span>Rughwani Enterprises, New Delhi &middot; GSTIN: 07FLOPR6641L1Z8</span>
                    </div>
                </div>
            </div>

            <!-- Column 2: Quick Links (Span 2) -->
            <div class="lg:col-span-2 space-y-3">
                <h4 class="font-semibold text-gray-900 text-xs uppercase tracking-wider border-b border-gray-100 pb-2">
                    Quick Links
                </h4>
                <ul class="space-y-2.5 text-xs text-gray-600 font-medium">
                    <li><a href="<?= url('/') ?>" class="hover:text-red-600 transition">Home</a></li>
                    <li><a href="<?= url('shop') ?>" class="hover:text-red-600 transition">Shop All Accessories</a></li>
                    <li><a href="<?= url('brands') ?>" class="hover:text-red-600 transition">Scooter Brands</a></li>
                    <li><a href="<?= url('categories') ?>" class="hover:text-red-600 transition">Product Categories</a>
                    </li>
                    <li><a href="<?= url('about-us') ?>" class="hover:text-red-600 transition">About ImportWale</a></li>
                    <li><a href="<?= url('contact-us') ?>" class="hover:text-red-600 transition">Contact Us</a></li>
                </ul>
            </div>

            <!-- Column 3: Policies & Customer Care (Span 3) -->
            <div class="lg:col-span-3 space-y-3">
                <h4 class="font-semibold text-gray-900 text-xs uppercase tracking-wider border-b border-gray-100 pb-2">
                    Policies &amp; Legal
                </h4>
                <ul class="space-y-2.5 text-xs text-gray-600 font-medium">
                    <li><a href="<?= url('support') ?>" class="hover:text-red-600 transition">Help Center &amp; FAQs</a>
                    </li>
                    <li><a href="<?= url('shipping-policy') ?>" class="hover:text-red-600 transition">Shipping &amp;
                            Delivery Policy</a></li>
                    <li><a href="<?= url('refund-policy') ?>" class="hover:text-red-600 transition">Returns &amp;
                            Replacement Policy</a></li>
                    <li><a href="<?= url('cancellation-policy') ?>" class="hover:text-red-600 transition">Cancellation
                            Policy</a></li>
                    <li><a href="<?= url('privacy-policy') ?>" class="hover:text-red-600 transition">Privacy Policy</a>
                    </li>
                    <li><a href="<?= url('terms-and-conditions') ?>" class="hover:text-red-600 transition">Terms &amp;
                            Conditions</a></li>
                </ul>
            </div>

            <!-- Column 4: Customer Portal (Span 3) -->
            <div class="lg:col-span-3 space-y-3">
                <h4 class="font-semibold text-gray-900 text-xs uppercase tracking-wider border-b border-gray-100 pb-2">
                    Customer Portal
                </h4>
                <ul class="space-y-2.5 text-xs text-gray-600 font-medium">
                    <li><a href="<?= url('account') ?>"
                            class="hover:text-red-600 transition flex items-center space-x-1.5"><i data-lucide="user"
                                class="w-3.5 h-3.5 text-red-600"></i><span>My Account</span></a></li>
                    <li><a href="<?= url('wishlist') ?>"
                            class="hover:text-red-600 transition flex items-center space-x-1.5"><i data-lucide="heart"
                                class="w-3.5 h-3.5 text-red-600"></i><span>Saved Wishlist</span></a></li>
                    <li><a href="<?= url('compare') ?>"
                            class="hover:text-red-600 transition flex items-center space-x-1.5"><i
                                data-lucide="git-compare" class="w-3.5 h-3.5 text-red-600"></i><span>Compare
                                Products</span></a></li>
                    <li><a href="<?= url('cart') ?>"
                            class="hover:text-red-600 transition flex items-center space-x-1.5"><i
                                data-lucide="shopping-cart" class="w-3.5 h-3.5 text-red-600"></i><span>Shopping
                                Cart</span></a></li>
                    <li><a href="https://wa.me/919217714452?text=Hi%20ImportWale%2C%20I%20need%20help%20with%20my%20order."
                            target="_blank" rel="noopener noreferrer"
                            class="text-emerald-600 hover:text-emerald-700 transition flex items-center space-x-1.5">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                            </svg>
                            <span>WhatsApp: 92177 14452</span>
                        </a></li>
                </ul>
            </div>

        </div>

        <!-- Bottom Bar: Copyright + Sleek Payment Badges -->
        <div class="bg-gray-50 border-t border-gray-900 py-5 text-gray-500">
            <div class="container mx-auto px-4 flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-xs font-medium">© <?= date('Y') ?> ImportWale (Rughwani Enterprises). GSTIN: 07FLOPR6641L1Z8.
                    All Rights Reserved.</p>

                <!-- Professional Payment Icons & Badges -->
                <div class="flex items-center flex-wrap gap-2 text-xs">
                    <span
                        class="bg-white border border-gray-900 px-2.5 py-1 rounded-lg text-blue-600 font-extrabold text-[11px] shadow-2xs">
                        Razorpay
                    </span>
                    <span
                        class="bg-white border border-gray-900 px-2.5 py-1 rounded-lg text-emerald-700 font-extrabold text-[11px] shadow-2xs">
                        UPI
                    </span>
                    <span
                        class="bg-white border border-gray-900 px-2.5 py-1 rounded-lg text-purple-700 font-extrabold text-[11px] shadow-2xs">
                        PhonePe
                    </span>
                    <span
                        class="bg-white border border-gray-900 px-2.5 py-1 rounded-lg text-cyan-600 font-extrabold text-[11px] shadow-2xs">
                        Paytm
                    </span>
                    <span
                        class="bg-white border border-gray-900 px-2.5 py-1 rounded-lg text-gray-800 font-semibold text-[11px] shadow-2xs">
                        <span class="text-blue-500">G</span><span class="text-red-500">P</span><span
                            class="text-amber-500">a</span><span class="text-green-500">y</span>
                    </span>
                    <span
                        class="bg-white border border-gray-900 px-2.5 py-1 rounded-lg text-blue-900 font-black tracking-wider text-[11px] shadow-2xs">
                        VISA
                    </span>
                    <span
                        class="bg-white border border-gray-900 px-2.5 py-1 rounded-lg text-gray-800 font-semibold text-[11px] shadow-2xs inline-flex items-center space-x-1">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-600 inline-block"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 inline-block -ml-2"></span>
                        <span class="ml-1 text-[10px]">Mastercard</span>
                    </span>
                    <span
                        class="bg-green-50 border border-green-200 px-2.5 py-1 rounded-lg text-green-700 font-semibold text-[11px] shadow-2xs">
                        ✓ COD Available
                    </span>
                </div>
            </div>
        </div>

    </div><!-- /hidden md:block desktop footer -->

</footer>


<!-- SECTION 11: PERSISTENT FLOATING COMPARE BAR -->
<?php
$compareList = $_SESSION['compare'] ?? [];
$compareCount = count($compareList);
?>
<div id="floating-compare-bar"
    class="<?= $compareCount >= 2 ? '' : 'hidden' ?> fixed bottom-[76px] sm:bottom-4 left-4 right-4 sm:left-auto sm:right-auto sm:left-1/2 sm:-translate-x-1/2 z-50 bg-gray-900 text-white px-3 sm:px-6 py-3 rounded-2xl shadow-2xl border border-gray-800 flex items-center space-x-2 sm:space-x-4 sm:max-w-xl w-auto sm:w-full">
    <div class="flex items-center space-x-1.5 shrink-0">
        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full text-white font-semibold text-xs flex items-center justify-center"
            style="background-color: var(--color-primary);">
            <span id="compare-bar-count"><?= $compareCount ?></span>/4
        </div>
        <span class="text-xs font-semibold hidden sm:inline">Products Selected</span>
    </div>

    <div class="flex-1 flex items-center justify-center min-w-0">
        <a href="<?= url('compare') ?>"
            class="h-9 sm:h-10 px-3 sm:px-6 text-white font-semibold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-1 sm:space-x-1.5 bg-red-600 hover:bg-red-700 active:bg-red-800 truncate max-w-full">
            <i data-lucide="git-compare" class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0"></i>
            <span class="truncate">Compare Now</span>
            <span class="hidden sm:inline truncate"> Products</span>
        </a>
    </div>

    <button onclick="clearCompareList()"
        class="text-xs text-gray-400 hover:text-white font-semibold shrink-0">Clear</button>
</div>

<!-- Slide-Over Drawer Cart -->
<div x-show="cartDrawer" class="relative z-[99999]" x-effect="document.body.classList.toggle('modal-open', cartDrawer)"
    x-cloak>
    <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity z-[99999]"
        @click="cartDrawer = false"></div>
    <div class="fixed inset-y-0 right-0 max-w-full flex sm:pl-10 z-[100000]">
        <div class="w-screen max-w-full sm:max-w-md bg-white dark:bg-slate-900 shadow-2xl flex flex-col">
            <div class="p-4 bg-gray-900 text-white flex items-center justify-between border-b border-gray-800">
                <h2 class="text-sm font-semibold flex items-center space-x-2">
                    <i data-lucide="shopping-bag" class="w-4 h-4 text-red-500"></i>
                    <span>Shopping Cart</span>
                </h2>
                <button @click="cartDrawer = false" class="text-gray-400 hover:text-white p-1 rounded-lg transition"><i
                        data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <div id="drawer-cart-items" class="flex-1 overflow-y-auto p-4 space-y-3">
                <?php if (empty($_SESSION['cart'])): ?>
                    <div class="text-center py-16 space-y-3">
                        <i data-lucide="shopping-bag" class="w-12 h-12 text-gray-300 dark:text-slate-600 mx-auto"></i>
                        <p class="text-xs text-gray-500 dark:text-slate-400 font-semibold">Your cart is currently empty.</p>
                        <a href="<?= url('shop') ?>" @click="cartDrawer = false"
                            class="inline-block px-6 py-2.5 text-white font-semibold text-xs rounded-xl bg-red-600 hover:bg-red-700 active:bg-red-800">Start
                            Shopping</a>
                    </div>
                <?php else: ?>
                    <?php
                    $drawerTotal = 0;
                    foreach ($_SESSION['cart'] as $item):
                        $itemTotal = ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
                        $drawerTotal += $itemTotal;
                        $imgSrc = asset($item['image'] ?? '');
                        ?>
                        <div
                            class="flex items-center justify-between border border-gray-100 dark:border-slate-800 rounded-xl p-3 bg-white dark:bg-slate-800/80 gap-3 shadow-2xs">
                            <div
                                class="w-14 h-14 bg-gray-50 dark:bg-slate-900 rounded-lg border border-gray-200 dark:border-slate-700 shrink-0 overflow-hidden flex items-center justify-center p-1">
                                <img src="<?= htmlspecialchars($imgSrc) ?>"
                                    onerror="this.onerror=null; this.src='<?= asset('assets/images/placeholder.jpg') ?>';"
                                    class="w-full h-full object-contain max-w-full max-h-full rounded-md">
                            </div>
                            <div class="flex-1 min-w-0 space-y-1">
                                <h4 class="text-xs font-semibold text-gray-900 dark:text-white line-clamp-1">
                                    <?= htmlspecialchars($item['name']) ?></h4>

                                <div class="flex items-center justify-between gap-2">
                                    <div
                                        class="inline-flex items-center border border-gray-200 dark:border-slate-700 rounded-lg bg-gray-50 dark:bg-slate-900 p-0.5 space-x-1">
                                        <form action="<?= url('cart/update') ?>" method="POST" class="inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                            <input type="hidden" name="quantity" value="<?= max(0, $item['quantity'] - 1) ?>">
                                            <button type="submit"
                                                class="w-5 h-5 rounded bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 text-gray-800 dark:text-slate-200 font-semibold hover:bg-gray-100 flex items-center justify-center text-xs transition"
                                                title="Decrease quantity">−</button>
                                        </form>
                                        <span
                                            class="w-5 text-center font-semibold text-[11px] text-gray-900 dark:text-white"><?= $item['quantity'] ?></span>
                                        <form action="<?= url('cart/update') ?>" method="POST" class="inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                            <input type="hidden" name="quantity" value="<?= $item['quantity'] + 1 ?>">
                                            <button type="submit"
                                                class="w-5 h-5 rounded bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 text-gray-800 dark:text-slate-200 font-semibold hover:bg-gray-100 flex items-center justify-center text-xs transition"
                                                title="Increase quantity">+</button>
                                        </form>
                                    </div>
                                    <span
                                        class="text-xs font-semibold text-red-600 dark:text-red-400"><?= format_price($itemTotal) ?></span>
                                </div>
                            </div>

                            <!-- Remove Single Item Button -->
                            <form action="<?= url('cart/remove') ?>" method="POST" class="shrink-0">
                                <?= csrf_field() ?>
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <button type="submit"
                                    class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition"
                                    title="Remove item">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div id="drawer-cart-footer"
                class="<?= empty($_SESSION['cart']) ? 'hidden' : '' ?> p-4 bg-gray-50 dark:bg-slate-800/90 border-t border-gray-200 dark:border-slate-700 space-y-3">
                <div class="flex items-center justify-between text-xs font-semibold text-gray-900 dark:text-white">
                    <span>Cart Subtotal</span>
                    <span class="text-sm text-red-600 dark:text-red-400"><?= format_price($drawerTotal ?? 0) ?></span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <a href="<?= url('cart') ?>"
                        class="h-10 border border-red-600 bg-white text-red-600 hover:bg-red-600 hover:text-white dark:border-red-500 dark:bg-slate-900 dark:text-red-400 dark:hover:bg-red-600 dark:hover:text-white text-center font-semibold text-xs rounded-xl transition flex items-center justify-center">View
                        Cart</a>
                    <a href="<?= url('checkout') ?>"
                        class="h-10 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white hover:text-white text-center font-semibold text-xs rounded-xl shadow-md transition flex items-center justify-center">Checkout
                        →</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SECTION 4: NON-BLOCKING GUEST CART ADD TOAST -->
<div x-show="cartPromptToast"
    class="fixed bottom-20 left-4 right-4 sm:bottom-6 sm:left-6 sm:right-auto z-50 max-w-sm bg-gray-900 text-white p-4 rounded-2xl shadow-2xl border border-gray-800 space-y-2 transition-all transform"
    x-cloak>
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <i data-lucide="check-circle" class="w-4 h-4 text-green-400"></i>
            <span class="font-semibold text-xs">Added to Cart!</span>
        </div>
        <button @click="cartPromptToast = false" class="text-gray-400 hover:text-white"><i data-lucide="x"
                class="w-4 h-4"></i></button>
    </div>
    <p class="text-[11px] text-gray-300 font-medium">Create an account or login to save your cart items permanently &
        receive order updates.</p>
    <div class="flex items-center space-x-2 pt-1">
        <a href="<?= url('login') ?>"
            class="h-8 px-4 bg-red-600 hover:bg-red-700 text-white text-[11px] font-semibold rounded-lg transition flex items-center justify-center">Login
            Now</a>
        <button @click="cartPromptToast = false"
            class="h-8 px-3 text-gray-400 hover:text-white text-[11px] font-semibold">Dismiss</button>
    </div>
</div>

<!-- Universal Scripts & Toast Notifications -->
<script src="<?= asset('assets/js/toast.js') ?>"></script>
<script src="<?= asset('assets/js/logout-modal.js') ?>"></script>

<!-- SECTION 4: AJAX ADD TO CART & WISHLIST HANDLERS -->
<script>
    // Dynamic Cart Drawer UI Updater
    function renderCartDrawerUI(cart) {
        if (!cart) return;

        const itemCount = cart.item_count || 0;

        // 1. Update Header Cart Badges
        const cartBadges = document.querySelectorAll('#header-cart-count, .cart-count-badge');
        cartBadges.forEach(b => {
            b.innerText = itemCount;
            if (itemCount > 0) b.classList.remove('hidden');
            else b.classList.add('hidden');
        });

        const itemsContainer = document.getElementById('drawer-cart-items');
        const footerContainer = document.getElementById('drawer-cart-footer');

        if (!itemsContainer) return;

        if (!cart.items || cart.items.length === 0) {
            itemsContainer.innerHTML = `
                    <div class="text-center py-16 space-y-3">
                        <i data-lucide="shopping-bag" class="w-12 h-12 text-gray-300 mx-auto"></i>
                        <p class="text-xs text-gray-500 font-semibold">Your cart is currently empty.</p>
                        <a href="<?= url('shop') ?>" onclick="const a = Alpine.$data(document.body); if(a) a.cartDrawer=false;" class="inline-block px-6 py-2.5 bg-red-600 text-white font-semibold text-xs rounded-lg">Start Shopping</a>
                    </div>
                `;
            if (footerContainer) footerContainer.classList.add('hidden');
        } else {
            let html = '';
            cart.items.forEach(item => {
                const lineTotal = '₹' + (item.price * item.quantity).toFixed(2);
                const itemPrice = '₹' + (parseFloat(item.price)).toFixed(2);
                const fallbackImg = 'https://via.placeholder.com/60?text=No+Image';
                const imgUrl = item.image || fallbackImg;
                const prevQty = Math.max(0, item.quantity - 1);
                const nextQty = item.quantity + 1;

                html += `
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3 gap-3">
                            <div class="w-14 h-14 bg-gray-50 rounded-lg border border-gray-900 shrink-0 overflow-hidden flex items-center justify-center p-1">
                                <img src="${imgUrl}" onerror="this.onerror=null; this.src='${fallbackImg}';" class="w-full h-full object-contain max-w-full max-h-full rounded-md">
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-xs font-semibold text-gray-900 truncate">${item.name}</h4>
                                <div class="flex items-center space-x-2 mt-1">
                                    <div class="inline-flex items-center border border-gray-900 rounded-lg bg-gray-50 p-0.5 space-x-1">
                                        <form action="<?= url('cart/update') ?>" method="POST" class="inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="product_id" value="${item.id}">
                                            <input type="hidden" name="quantity" value="${prevQty}">
                                            <button type="submit" class="w-5 h-5 rounded bg-white border border-gray-900 text-gray-800 font-semibold hover:bg-gray-100 flex items-center justify-center text-xs transition" title="Decrease quantity">−</button>
                                        </form>
                                        <span class="w-5 text-center font-semibold text-[11px] text-gray-900">${item.quantity}</span>
                                        <form action="<?= url('cart/update') ?>" method="POST" class="inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="product_id" value="${item.id}">
                                            <input type="hidden" name="quantity" value="${nextQty}">
                                            <button type="submit" class="w-5 h-5 rounded bg-white border border-gray-900 text-gray-800 font-semibold hover:bg-gray-100 flex items-center justify-center text-xs transition" title="Increase quantity">+</button>
                                        </form>
                                    </div>
                                    <span class="text-[11px] text-gray-400">× ${itemPrice}</span>
                                </div>
                                <p class="text-xs font-semibold text-theme-primary mt-0.5">${lineTotal}</p>
                            </div>
                            <form action="<?= url('cart/remove') ?>" method="POST" class="shrink-0">
                                <?= csrf_field() ?>
                                <input type="hidden" name="product_id" value="${item.id}">
                                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Remove item">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    `;
            });
            itemsContainer.innerHTML = html;
            if (footerContainer) footerContainer.classList.remove('hidden');
        }

        if (window.lucide) lucide.createIcons();
    }

    // 1. Universal Add to Cart Form Handler with Full Headers & CSRF Token
    function handleAddToCartSubmit(formElem) {
        if (formElem.dataset.submitting === 'true') return;
        formElem.dataset.submitting = 'true';

        const formData = new FormData(formElem);
        const bodyParams = new URLSearchParams(formData);

        if (!bodyParams.has('_csrf_token')) {
            bodyParams.append('_csrf_token', '<?= csrf_token() ?>');
        }

        const submitBtn = formElem.querySelector('button[type="submit"]');
        const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';

        if (submitBtn) submitBtn.disabled = true;

        fetch('<?= url('cart/add') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '<?= csrf_token() ?>'
            },
            body: bodyParams.toString()
        })
            .then(r => {
                if (!r.ok) {
                    return r.text().then(text => { throw new Error(text || 'Server error (' + r.status + ')'); });
                }
                return r.json();
            })
            .then(data => {
                if (submitBtn) submitBtn.disabled = false;

                if (data.success || data.cart) {
                    const cart = data.cart || {};
                    const itemCount = cart.item_count || 0;
                    renderCartDrawerUI(cart);

                    // 1. Update Header Cart Badges
                    const cartBadges = document.querySelectorAll('#header-cart-count, .cart-count-badge');
                    cartBadges.forEach(b => {
                        b.innerText = itemCount;
                        if (itemCount > 0) b.classList.remove('hidden');
                        else b.classList.add('hidden');
                    });

                    // 2. Pulse / Bounce Header Cart Icon
                    const headerCartIcon = document.querySelector('#header-cart-icon, [data-lucide="shopping-bag"]');
                    if (headerCartIcon) {
                        headerCartIcon.style.transition = 'all 0.3s ease';
                        headerCartIcon.style.transform = 'scale(1.3)';
                        setTimeout(() => { headerCartIcon.style.transform = 'scale(1)'; }, 400);
                    }

                    // 3. Temporarily change button to "✓ Added!"
                    if (submitBtn) {
                        submitBtn.classList.add('bg-green-600');
                        submitBtn.innerHTML = `<span>✓ Added to Cart!</span>`;
                        setTimeout(() => {
                            submitBtn.classList.remove('bg-green-600');
                            submitBtn.innerHTML = originalBtnHtml;
                        }, 1800);
                    }

                    // 4. Non-blocking Toast Notification (Dismisses in 2s)
                    if (window.MudsorToast) {
                        window.MudsorToast.show('Added to cart!', 'success', 2000);
                    }

                    // Guest Prompt Toast Check
                    <?php if (empty($_SESSION['user_id'])): ?>
                        const cartPrompted = sessionStorage.getItem('mudsor_cart_prompted');
                        if (!cartPrompted) {
                            sessionStorage.setItem('mudsor_cart_prompted', '1');
                            const alpineData = Alpine.$data(document.body);
                            if (alpineData) alpineData.cartPromptToast = true;
                        }
                    <?php endif; ?>
                } else {
                    if (window.MudsorToast) window.MudsorToast.show(data.error || data.message || 'Could not add to cart', 'error');
                }
            })
            .catch(err => {
                if (submitBtn) submitBtn.disabled = false;
                console.error("Cart Add Error:", err);
                if (window.MudsorToast) window.MudsorToast.show(err.message || 'Error adding to cart', 'error');
            })
            .finally(() => {
                formElem.dataset.submitting = 'false';
            });
    }

    // Global Event Interceptor for form submits
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form || !form.action) return;

        if (form.action.includes('cart/add')) {
            e.preventDefault();
            handleAddToCartSubmit(form);
        }
    });

    // 2. Live Session Heartbeat (Every 40 seconds)
    function sendHeartbeat() {
        fetch('<?= url('api/heartbeat') ?>', { method: 'GET', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .catch(err => console.log('Heartbeat sync:', err));
    }
    sendHeartbeat();
    setInterval(sendHeartbeat, 40000);

    // 2. Global Smart Compare Toggle with Max 4 Items Limit Alert
    function removeCompareProduct(productId) {
        toggleCompareProduct(productId, null, true);
    }

    function toggleCompareProduct(productId, checkboxElem, autoReload = false) {
        fetch('<?= url('compare/toggle') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: 'product_id=' + productId + '&_csrf_token=<?= csrf_token() ?>'
        })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'limit_reached') {
                    if (window.MudsorToast) window.MudsorToast.show('Maximum 4 products can be compared at once!', 'warning');
                    else alert('Maximum 4 products can be compared at once!');
                    if (checkboxElem) checkboxElem.checked = false;
                } else {
                    const count = data.count || 0;
                    const compareBadges = document.querySelectorAll('#compare-badge-count, .compare-count-badge');
                    const barCount = document.getElementById('compare-bar-count');
                    const bar = document.getElementById('floating-compare-bar');

                    compareBadges.forEach(badge => {
                        badge.innerText = count;
                        if (count > 0) badge.classList.remove('hidden');
                        else badge.classList.add('hidden');
                    });
                    if (barCount) barCount.innerText = count;

                    if (bar) {
                        if (count >= 2) bar.classList.remove('hidden');
                        else bar.classList.add('hidden');
                    }

                    if (autoReload || window.location.pathname.includes('compare')) {
                        window.location.reload();
                    }
                }
            });
    }

    // 3. Clear Compare List – direct navigation, 100% reliable
    function clearCompareList() {
        // Immediately hide the floating bar for instant feedback
        const bar = document.getElementById('floating-compare-bar');
        if (bar) bar.classList.add('hidden');
        const compareBadges = document.querySelectorAll('#compare-badge-count, .compare-count-badge');
        compareBadges.forEach(b => { b.innerText = '0'; b.classList.add('hidden'); });
        // Navigate to clear endpoint – server clears session and redirects back
        window.location.href = '<?= url('compare/clear') ?>';
    }

    // 4. Global Guest & User Wishlist Toggle (0ms Instant Optimistic UI + Smooth Card Removal)
    function toggleWishlist(productId, btnElem) {
        if (!productId) return;

        let targetBtns = document.querySelectorAll(`[data-wishlist-id="${productId}"]`);
        if (targetBtns.length === 0 && btnElem) {
            targetBtns = [btnElem];
        }

        // Disable pointer events during click processing to prevent rapid double-clicks
        targetBtns.forEach(b => b.style.pointerEvents = 'none');

        // Determine current state from target button
        const firstBtn = targetBtns[0] || btnElem;
        const isCurrentlyWished = firstBtn ? (firstBtn.classList.contains('text-red-600') || firstBtn.title === 'Remove from Wishlist') : false;
        const optimisticStatus = isCurrentlyWished ? 'removed' : 'added';

        // 1. INSTANT OPTIMISTIC UI TOGGLE (0ms Latency!)
        targetBtns.forEach(targetBtn => {
            const heartIcon = targetBtn.querySelector('svg, i') || targetBtn;
            heartIcon.style.transition = 'all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275)';

            if (optimisticStatus === 'added') {
                heartIcon.style.transform = 'scale(1.35)';
                heartIcon.setAttribute('fill', '#A8111C');
                heartIcon.style.fill = '#A8111C';
                heartIcon.style.color = '#A8111C';
                targetBtn.classList.add('text-red-600');
                targetBtn.classList.remove('text-gray-400', 'text-gray-500');
                targetBtn.title = 'Remove from Wishlist';
                setTimeout(() => { heartIcon.style.transform = 'scale(1)'; }, 300);
            } else {
                heartIcon.style.transform = 'scale(0.8)';
                heartIcon.setAttribute('fill', 'none');
                heartIcon.style.fill = 'none';
                heartIcon.style.color = 'currentColor';
                targetBtn.classList.remove('text-red-600');
                targetBtn.classList.add('text-gray-400');
                targetBtn.title = 'Save to Wishlist';
                setTimeout(() => { heartIcon.style.transform = 'scale(1)'; }, 300);
            }
        });

        // Smoothly remove card element from DOM on wishlist page if removed
        if (optimisticStatus === 'removed') {
            const wishCards = document.querySelectorAll(`[data-wishlist-card="${productId}"]`);
            wishCards.forEach(card => {
                card.style.transition = 'all 0.3s ease-out';
                card.style.opacity = '0';
                card.style.transform = 'translateY(-10px) scale(0.96)';
                setTimeout(() => {
                    card.remove();
                    const remaining = document.querySelectorAll('[data-wishlist-card]');
                    const gridContainer = document.getElementById('wishlist-grid-container');
                    const emptyContainer = document.getElementById('wishlist-empty-container');
                    if (remaining.length === 0 && gridContainer && emptyContainer) {
                        gridContainer.classList.add('hidden');
                        emptyContainer.classList.remove('hidden');
                    }
                }, 300);
            });
        }

        // Synchronously update header count badges
        const wishlistBadges = document.querySelectorAll('#header-wishlist-count, .wishlist-count-badge');
        wishlistBadges.forEach(b => {
            let curCount = parseInt(b.innerText) || 0;
            let newCount = Math.max(0, curCount + (optimisticStatus === 'added' ? 1 : -1));
            b.innerText = newCount;
            if (newCount > 0) b.classList.remove('hidden');
            else b.classList.add('hidden');
        });

        if (window.MudsorToast) {
            window.MudsorToast.show(optimisticStatus === 'added' ? 'Added to wishlist!' : 'Removed from wishlist', 'success', 1800);
        }

        setTimeout(() => {
            targetBtns.forEach(b => b.style.pointerEvents = 'auto');
        }, 350);

        // 2. ASYNCHRONOUS BACKGROUND SERVER SYNC
        fetch('<?= url('wishlist/toggle') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: 'product_id=' + productId + '&_csrf_token=<?= csrf_token() ?>'
        })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'added' || data.status === 'removed') {
                    // Sync verified server count
                    wishlistBadges.forEach(b => {
                        b.innerText = data.count || '';
                        if (data.count > 0) b.classList.remove('hidden');
                        else b.classList.add('hidden');
                    });
                }
            })
            .catch(err => {
                console.warn('Wishlist sync:', err);
            });
    }
</script>

<?php
if (!isset($wishlistCount)) {
    $wishlistCount = 0;
    if (!empty($_SESSION['user_id'])) {
        try {
            $db = \App\Core\Database::getInstance();
            $wStmt = $db->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
            $wStmt->execute([$_SESSION['user_id']]);
            $wishlistCount = (int) $wStmt->fetchColumn();
        } catch (\Throwable $e) {
        }
    } else {
        $wishlistCount = count($_SESSION['guest_wishlist'] ?? []);
    }
}
if (!isset($cartCount)) {
    $cartCount = 0;
    if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $cItem) {
            $cartCount += (int) ($cItem['quantity'] ?? 1);
        }
    }
}
$currentUri = $_SERVER['REQUEST_URI'] ?? '';
$isHome = (rtrim(parse_url($currentUri, PHP_URL_PATH), '/') === '' || str_ends_with(parse_url($currentUri, PHP_URL_PATH), 'public') || str_ends_with(parse_url($currentUri, PHP_URL_PATH), 'index.php'));
$isShop = (strpos($currentUri, 'shop') !== false);
$isCat = (strpos($currentUri, 'categories') !== false);
$isWish = (strpos($currentUri, 'wishlist') !== false);
$isAccount = (strpos($currentUri, 'account') !== false || strpos($currentUri, 'login') !== false);

$isLoggedIn = !empty($_SESSION['user_id']);
$userAccountUrl = $isLoggedIn ? url('account') : url('login');
$userAccountLabel = $isLoggedIn ? 'Account' : 'Login';
?>

<!-- Floating Sitewide "Need Help?" WhatsApp Button -->
<a href="https://wa.me/919217714452?text=Hi%20ImportWale%2C%20I%20need%20help%20with%20a%20wholesale%20order." 
   target="_blank" rel="noopener noreferrer"
   class="fixed bottom-20 right-5 md:bottom-8 md:right-8 z-[999] px-4 py-2.5 bg-[#f05a29] text-white font-bold text-xs rounded-full shadow-lg hover:bg-orange-600 transition-all duration-300 flex items-center space-x-2 border border-white/20 hover:scale-105 active:scale-95">
    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
    </svg>
    <span>Need Help?</span>
</a>

<!-- NATIVE MOBILE BOTTOM APP NAVIGATION BAR -->
<?php $isCompare = (strpos($currentUri, 'compare') !== false); ?>
<nav class="fixed bottom-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-md border-t border-gray-900 shadow-2xl md:hidden flex items-center justify-around h-16 px-0 text-gray-700 font-sans"
    style="box-sizing: border-box;">
    <!-- 1. Home -->
    <a href="<?= url('/') ?>"
        class="flex flex-col items-center justify-center flex-1 h-full py-1 transition <?= $isHome ? 'text-red-600 font-semibold' : 'text-gray-600 hover:text-red-600' ?>"
        style="min-width: 40px; min-height: 44px;">
        <svg class="w-4 h-4 mb-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
        <span class="text-[9px] tracking-tight">Home</span>
    </a>

    <!-- 2. Shop -->
    <a href="<?= url('shop') ?>"
        class="flex flex-col items-center justify-center flex-1 h-full py-1 transition <?= $isShop ? 'text-red-600 font-semibold' : 'text-gray-600 hover:text-red-600' ?>"
        style="min-width: 40px; min-height: 44px;">
        <svg class="w-4 h-4 mb-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h18v4H3zM3 7l2 12h14l2-12M10 11h4" />
        </svg>
        <span class="text-[9px] tracking-tight">Shop</span>
    </a>

    <!-- 3. Profile / Account -->
    <a href="<?= $userAccountUrl ?>"
        class="flex flex-col items-center justify-center flex-1 h-full py-1 transition <?= $isAccount ? 'text-red-600 font-semibold' : 'text-gray-600 hover:text-red-600' ?>"
        style="min-width: 40px; min-height: 44px;">
        <svg class="w-4 h-4 mb-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
        <span class="text-[9px] tracking-tight"><?= $userAccountLabel ?></span>
    </a>

    <!-- 4. Compare -->
    <?php $compareNavCount = count($_SESSION['compare'] ?? []); ?>
    <a href="<?= url('compare') ?>"
        class="flex flex-col items-center justify-center flex-1 h-full py-1 transition relative <?= $isCompare ? 'text-red-600 font-semibold' : 'text-gray-600 hover:text-red-600' ?>"
        style="min-width: 40px; min-height: 44px;">
        <div class="relative">
            <svg class="w-4 h-4 mb-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h7v14H3V5zm11 0h7v14h-7V5z" />
            </svg>
            <?php if ($compareNavCount > 0): ?>
                <span
                    class="absolute -top-1.5 -right-2.5 bg-red-600 text-white text-[9px] font-semibold min-w-[15px] h-[15px] px-0.5 rounded-full flex items-center justify-center border border-white leading-none"><?= $compareNavCount ?></span>
            <?php endif; ?>
        </div>
        <span class="text-[9px] tracking-tight">Compare</span>
    </a>

    <!-- 5. WhatsApp -->
    <a href="https://wa.me/919217714452?text=Hi%20ImportWale%2C%20I%20am%20interested%20in%20wholesale%20scooter%20accessories."
        target="_blank" rel="noopener noreferrer"
        class="flex flex-col items-center justify-center flex-1 h-full py-1 text-emerald-600 hover:text-emerald-700 transition"
        style="min-width: 40px; min-height: 44px;">
        <svg class="w-4 h-4 mb-0.5" fill="currentColor" viewBox="0 0 24 24">
            <path
                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
        </svg>
        <span class="text-[9px] tracking-tight">WhatsApp</span>
    </a>

    <!-- 6. Cart -->
    <button type="button" @click="cartDrawer = true"
        class="flex flex-col items-center justify-center flex-1 h-full py-1 text-gray-600 hover:text-red-600 transition relative focus:outline-none cursor-pointer"
        style="min-width: 40px; min-height: 44px;">
        <div class="relative">
            <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <span
                class="<?= $cartCount > 0 ? '' : 'hidden' ?> cart-count-badge absolute -top-1.5 -right-2.5 bg-red-600 text-white text-[9px] font-semibold min-w-[16px] h-[16px] px-1 rounded-full flex items-center justify-center border border-white leading-none"><?= $cartCount ?></span>
        </div>
        <span class="text-[10px] tracking-tight">Cart</span>
    </button>
</nav>

<!-- Swiper 11 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof lucide !== 'undefined' && lucide.createIcons) {
            lucide.createIcons();
        }

        if (typeof Swiper !== 'undefined') {

            // 1. Categories Swiper (2 per row on mobile, like reference design)
            if (document.querySelector('.swiper-categories')) {
                new Swiper('.swiper-categories', {
                    slidesPerView: 2,
                    spaceBetween: 12,
                    grabCursor: true,
                    navigation: {
                        nextEl: '#cat-next',
                        prevEl: '#cat-prev',
                    },
                    breakpoints: {
                        481: { slidesPerView: 2, spaceBetween: 14 },
                        768: { slidesPerView: 5.2, spaceBetween: 14 },
                        1025: { slidesPerView: 6, spaceBetween: 16 }
                    }
                });
            }

            // 2. Best Sellers Swiper (2 per row on mobile)
            if (document.querySelector('.swiper-bestsellers')) {
                new Swiper('.swiper-bestsellers', {
                    slidesPerView: 2,
                    spaceBetween: 12,
                    grabCursor: true,
                    navigation: {
                        nextEl: '#bestsellers-next',
                        prevEl: '#bestsellers-prev',
                    },
                    breakpoints: {
                        481: { slidesPerView: 2, spaceBetween: 14 },
                        768: { slidesPerView: 3.2, spaceBetween: 16 },
                        1025: { slidesPerView: 4, spaceBetween: 16 },
                        1441: { slidesPerView: 5, spaceBetween: 16 }
                    }
                });
            }

            // 3. Featured Products Swiper (2 per row on mobile)
            if (document.querySelector('.swiper-featured')) {
                const featuredSwiper = new Swiper('.swiper-featured', {
                    slidesPerView: 2,
                    spaceBetween: 12,
                    grabCursor: true,
                    navigation: {
                        nextEl: '#featured-next',
                        prevEl: '#featured-prev',
                    },
                    breakpoints: {
                        481: { slidesPerView: 2, spaceBetween: 14 },
                        768: { slidesPerView: 2.5, spaceBetween: 16 },
                        1025: { slidesPerView: 3.5, spaceBetween: 16 }
                    },
                    on: {
                        init: syncPromoCardHeight,
                        resize: syncPromoCardHeight
                    }
                });
            }

            function syncPromoCardHeight() {
                const rightCol = document.querySelector('.swiper-featured');
                const promoCard = document.querySelector('.featured-promo-card');
                if (!rightCol || !promoCard) return;
                promoCard.style.height = '';
                if (window.innerWidth >= 1024) {
                    const h = rightCol.offsetHeight;
                    if (h > 0) promoCard.style.height = h + 'px';
                }
            }
            // Also sync on window load after images are loaded
            window.addEventListener('load', syncPromoCardHeight);
            window.addEventListener('resize', syncPromoCardHeight);

            // 4. New Arrivals Swiper (2 per row on mobile)
            if (document.querySelector('.swiper-newarrivals')) {
                new Swiper('.swiper-newarrivals', {
                    slidesPerView: 2,
                    spaceBetween: 12,
                    grabCursor: true,
                    navigation: {
                        nextEl: '#newarrivals-next',
                        prevEl: '#newarrivals-prev',
                    },
                    breakpoints: {
                        481: { slidesPerView: 2, spaceBetween: 14 },
                        768: { slidesPerView: 3.2, spaceBetween: 16 },
                        1025: { slidesPerView: 4, spaceBetween: 16 },
                        1441: { slidesPerView: 5, spaceBetween: 16 }
                    }
                });
            }

            // 5. Videos Swiper (1 per row on mobile)
            if (document.querySelector('.swiper-videos')) {
                new Swiper('.swiper-videos', {
                    slidesPerView: 1,
                    spaceBetween: 16,
                    grabCursor: true,
                    navigation: {
                        nextEl: '#videos-next',
                        prevEl: '#videos-prev',
                    },
                    breakpoints: {
                        481: { slidesPerView: 1, spaceBetween: 16 },
                        768: { slidesPerView: 3.2, spaceBetween: 20 },
                        1025: { slidesPerView: 4, spaceBetween: 20 }
                    }
                });
            }

            // 6. Google Reviews Swiper (1 per row on mobile)
            if (document.querySelector('.swiper-reviews')) {
                new Swiper('.swiper-reviews', {
                    slidesPerView: 1,
                    spaceBetween: 16,
                    grabCursor: true,
                    navigation: {
                        nextEl: '#reviews-next',
                        prevEl: '#reviews-prev',
                    },
                    breakpoints: {
                        481: { slidesPerView: 1, spaceBetween: 16 },
                        768: { slidesPerView: 2.2, spaceBetween: 16 },
                        1025: { slidesPerView: 3, spaceBetween: 16 }
                    }
                });
            }

            // 7. Services Swiper — mobile only (md:hidden element)
            if (document.querySelector('.swiper-services')) {
                new Swiper('.swiper-services', {
                    slidesPerView: 1,
                    spaceBetween: 16,
                    grabCursor: true,
                    navigation: {
                        nextEl: '#services-next',
                        prevEl: '#services-prev',
                    },
                    breakpoints: {
                        481: { slidesPerView: 1, spaceBetween: 16 },
                        640: { slidesPerView: 1.6, spaceBetween: 16 }
                    }
                });
            }

            // 8. Brands Swiper — mobile (2 cards per row)
            if (document.querySelector('.swiper-brands')) {
                new Swiper('.swiper-brands', {
                    slidesPerView: 2,
                    spaceBetween: 12,
                    grabCursor: true,
                    navigation: {
                        nextEl: '#brands-next',
                        prevEl: '#brands-prev',
                    },
                    pagination: {
                        el: '.swiper-brands-pagination',
                        clickable: true,
                        dynamicBullets: true,
                    },
                    breakpoints: {
                        400: { slidesPerView: 2, spaceBetween: 12 },
                        481: { slidesPerView: 3.8, spaceBetween: 12 },
                        640: { slidesPerView: 4.5, spaceBetween: 14 }
                    }
                });
            }

            // 9. Articles Swiper (1 per row on mobile, 2 on tablet, 4 on desktop)
            if (document.querySelector('.swiper-articles')) {
                new Swiper('.swiper-articles', {
                    slidesPerView: 1,
                    spaceBetween: 16,
                    grabCursor: true,
                    navigation: {
                        nextEl: '#articles-next',
                        prevEl: '#articles-prev',
                    },
                    breakpoints: {
                        320: { slidesPerView: 1, spaceBetween: 16 },
                        640: { slidesPerView: 2, spaceBetween: 20 },
                        1024: { slidesPerView: 4, spaceBetween: 24 }
                    }
                });
            }

        }
    });

    if (typeof lucide !== 'undefined' && lucide.createIcons) {
        lucide.createIcons();
    }

    // Compare Scooters Swiper (1 card per row on mobile)
    document.addEventListener('alpine:initialized', function () {
        if (typeof lucide !== 'undefined' && lucide.createIcons) {
            lucide.createIcons();
        }
        if (typeof Swiper === 'undefined') return;
        var compareEl = document.querySelector('.swiper-compare');
        if (!compareEl) return;
        // Small delay to ensure Alpine x-for slides are fully in DOM
        setTimeout(function () {
            new Swiper('.swiper-compare', {
                slidesPerView: 1,
                spaceBetween: 16,
                grabCursor: true,
                loop: false,
                watchSlidesProgress: true,
                pagination: {
                    el: '.swiper-compare-pagination',
                    clickable: true,
                    dynamicBullets: true,
                },
                breakpoints: {
                    481: { slidesPerView: 1, spaceBetween: 16 },
                    768: { slidesPerView: 2.2, spaceBetween: 16 },
                    1025: { slidesPerView: 4, spaceBetween: 20 }
                }
            });
            if (typeof lucide !== 'undefined' && lucide.createIcons) {
                lucide.createIcons();
            }
        }, 150);
    });
</script>

</body>

</html>