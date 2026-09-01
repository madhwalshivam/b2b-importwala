<?php
$title = $pageTitle ?? "Help Center & FAQs | ImportWale Wholesale Support";
ob_start();
?>

<!-- Support & Help Center Page Header -->
<div style="background: linear-gradient(135deg, #111827 0%, #1f2937 100%); color:#fff; padding: 48px 24px; border-radius: 16px; margin-bottom: 32px; text-align: center; position: relative; overflow: hidden;">
  <div style="position: absolute; right: -30px; top: -30px; width: 160px; height: 160px; background: rgba(240, 90, 41, 0.15); border-radius: 50%; blur(40px);"></div>
  <div style="max-width: 720px; margin: 0 auto; position: relative; z-index: 2;">
    <span style="background: rgba(240, 90, 41, 0.2); color: #f05a29; border: 1px solid rgba(240, 90, 41, 0.4); padding: 4px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; display: inline-block; margin-bottom: 14px;">
      ImportWale Support Center
    </span>
    <h1 style="font-size: 32px; font-weight: 800; margin-bottom: 12px; font-family: var(--font-heading, sans-serif); color: #ffffff;">
      How Can We Help Your Business Today?
    </h1>
    <p style="font-size: 15px; color: #9ca3af; margin-bottom: 24px; line-height: 1.5;">
      Search our knowledge base for instant answers regarding wholesale orders, MOQs, express air shipping, GST invoicing, and return policies.
    </p>

    <!-- Instant Search Bar -->
    <div style="position: relative; max-width: 580px; margin: 0 auto;">
      <input type="text" id="faqSearchInput" onkeyup="filterFaqs()" placeholder="Type a question or keyword (e.g. MOQ, shipping, refund, GST)..." 
        style="width: 100%; padding: 14px 20px 14px 48px; border-radius: 12px; border: 1px solid #374151; background: #1f2937; color: #ffffff; font-size: 14px; font-family: inherit; outline: none; transition: border-color 0.2s, box-shadow 0.2s;"
        onfocus="this.style.borderColor='#f05a29'; this.style.boxShadow='0 0 0 3px rgba(240,90,41,0.2)'"
        onblur="this.style.borderColor='#374151'; this.style.boxShadow='none'">
      <svg style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); width: 20px; height: 20px; color: #9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
      </svg>
    </div>
  </div>
</div>

<!-- Quick Support Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px;">
  <a href="<?= url('contact-us') ?>" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 24px; text-decoration: none; color: inherit; transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s; display: flex; flex-direction: column; gap: 10px;"
     onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 25px -5px rgba(0,0,0,0.05)'; this.style.borderColor='#f05a29';"
     onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'; this.style.borderColor='#e5e7eb';">
    <div style="width: 44px; height: 44px; background: #fff4f0; color: #f05a29; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
      <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
    </div>
    <h3 style="font-size: 16px; font-weight: 700; color: #111827; margin: 0;">Contact Support</h3>
    <p style="font-size: 13px; color: #6b7280; margin: 0; line-height: 1.4;">Submit a ticket or query directly to our B2B team.</p>
    <span style="font-size: 13px; font-weight: 700; color: #f05a29; margin-top: auto; display: inline-flex; align-items: center; gap: 4px;">
      Get In Touch &rarr;
    </span>
  </a>

  <a href="<?= url('shipping-policy') ?>" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 24px; text-decoration: none; color: inherit; transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s; display: flex; flex-direction: column; gap: 10px;"
     onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 25px -5px rgba(0,0,0,0.05)'; this.style.borderColor='#f05a29';"
     onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'; this.style.borderColor='#e5e7eb';">
    <div style="width: 44px; height: 44px; background: #eff6ff; color: #2563eb; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
      <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
    </div>
    <h3 style="font-size: 16px; font-weight: 700; color: #111827; margin: 0;">Shipping & Air Freight</h3>
    <p style="font-size: 13px; color: #6b7280; margin: 0; line-height: 1.4;">Delivery timelines, express air cargo, customs & duty.</p>
    <span style="font-size: 13px; font-weight: 700; color: #2563eb; margin-top: auto; display: inline-flex; align-items: center; gap: 4px;">
      Read Policy &rarr;
    </span>
  </a>

  <a href="<?= url('refund-policy') ?>" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 24px; text-decoration: none; color: inherit; transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s; display: flex; flex-direction: column; gap: 10px;"
     onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 25px -5px rgba(0,0,0,0.05)'; this.style.borderColor='#f05a29';"
     onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'; this.style.borderColor='#e5e7eb';">
    <div style="width: 44px; height: 44px; background: #ecfdf5; color: #059669; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
      <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
    </div>
    <h3 style="font-size: 16px; font-weight: 700; color: #111827; margin: 0;">Refund & Replacements</h3>
    <p style="font-size: 13px; color: #6b7280; margin: 0; line-height: 1.4;">Damaged goods replacement & 7-day inspection window.</p>
    <span style="font-size: 13px; font-weight: 700; color: #059669; margin-top: auto; display: inline-flex; align-items: center; gap: 4px;">
      View Guidelines &rarr;
    </span>
  </a>

  <a href="https://wa.me/919217714452?text=Hi%20ImportWale%2C%20I%20have%20a%20support%20query" target="_blank" rel="noopener" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 24px; text-decoration: none; color: inherit; transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s; display: flex; flex-direction: column; gap: 10px;"
     onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 25px -5px rgba(0,0,0,0.05)'; this.style.borderColor='#10b981';"
     onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'; this.style.borderColor='#e5e7eb';">
    <div style="width: 44px; height: 44px; background: #ecfdf5; color: #10b981; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
      <svg width="22" height="22" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
    </div>
    <h3 style="font-size: 16px; font-weight: 700; color: #111827; margin: 0;">WhatsApp Live Chat</h3>
    <p style="font-size: 13px; color: #6b7280; margin: 0; line-height: 1.4;">Instant assistance from our B2B sales agents.</p>
    <span style="font-size: 13px; font-weight: 700; color: #10b981; margin-top: auto; display: inline-flex; align-items: center; gap: 4px;">
      Chat Now &rarr;
    </span>
  </a>
</div>

<!-- Main FAQ Categories Accordion Section -->
<div style="max-width: 900px; margin: 0 auto 60px auto;">
  <h2 style="font-size: 22px; font-weight: 800; color: #111827; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
    <svg width="24" height="24" fill="none" stroke="#f05a29" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    Frequently Asked Questions
  </h2>

  <div id="faqContainer" style="display: flex; flex-direction: column; gap: 16px;">

    <!-- Category 1: B2B Sourcing & Orders -->
    <div class="faq-group">
      <h3 style="font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #f05a29; margin-bottom: 12px;">
        1. B2B Ordering & Wholesale Sourcing
      </h3>

      <div class="faq-item" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;">
        <button onclick="toggleFaq(this)" style="width: 100%; text-align: left; padding: 18px 20px; font-size: 15px; font-weight: 700; color: #111827; background: #ffffff; border: none; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
          <span>What is the Minimum Order Quantity (MOQ) on ImportWale?</span>
          <span class="faq-icon" style="font-size: 18px; color: #6b7280;">+</span>
        </button>
        <div class="faq-answer" style="display: none; padding: 0 20px 20px 20px; font-size: 14px; color: #4b5563; line-height: 1.6; border-top: 1px dashed #f3f4f6;">
          At ImportWale, we support small, medium, and large B2B buyers! Many of our listed items have <strong>No MOQ</strong> or very low MOQs (e.g. 5–10 units). For custom factory sourcing, tiered volume discounts apply automatically at checkout.
        </div>
      </div>

      <div class="faq-item" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; margin-top: 10px;">
        <button onclick="toggleFaq(this)" style="width: 100%; text-align: left; padding: 18px 20px; font-size: 15px; font-weight: 700; color: #111827; background: #ffffff; border: none; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
          <span>Can I request a sample before placing a bulk order?</span>
          <span class="faq-icon" style="font-size: 18px; color: #6b7280;">+</span>
        </button>
        <div class="faq-answer" style="display: none; padding: 0 20px 20px 20px; font-size: 14px; color: #4b5563; line-height: 1.6; border-top: 1px dashed #f3f4f6;">
          Yes! You can order single sample pieces directly from the catalog. Additionally, you can request a <strong>Custom Quote</strong> using the "Get a Custom Quote" RFQ button in the top navigation bar to negotiate sample reimbursement for large bulk production runs.
        </div>
      </div>

      <div class="faq-item" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; margin-top: 10px;">
        <button onclick="toggleFaq(this)" style="width: 100%; text-align: left; padding: 18px 20px; font-size: 15px; font-weight: 700; color: #111827; background: #ffffff; border: none; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
          <span>How does the Visual Search feature work?</span>
          <span class="faq-icon" style="font-size: 18px; color: #6b7280;">+</span>
        </button>
        <div class="faq-answer" style="display: none; padding: 0 20px 20px 20px; font-size: 14px; color: #4b5563; line-height: 1.6; border-top: 1px dashed #f3f4f6;">
          Click the 📷 Camera Icon in our search bar to upload any product photo or screenshot. Our AI visual similarity engine will instantly compare image feature embeddings and list matching wholesale products available directly from source manufacturers.
        </div>
      </div>
    </div>

    <!-- Category 2: Shipping & Logistics -->
    <div class="faq-group" style="margin-top: 16px;">
      <h3 style="font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #2563eb; margin-bottom: 12px;">
        2. Shipping, Air Freight & Logistics
      </h3>

      <div class="faq-item" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;">
        <button onclick="toggleFaq(this)" style="width: 100%; text-align: left; padding: 18px 20px; font-size: 15px; font-weight: 700; color: #111827; background: #ffffff; border: none; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
          <span>How fast are wholesale orders dispatched?</span>
          <span class="faq-icon" style="font-size: 18px; color: #6b7280;">+</span>
        </button>
        <div class="faq-answer" style="display: none; padding: 0 20px 20px 20px; font-size: 14px; color: #4b5563; line-height: 1.6; border-top: 1px dashed #f3f4f6;">
          In-stock wholesale orders are processed and dispatched within <strong>24 to 48 hours</strong>. Free Air Shipping orders are dispatched via express air courier and typically arrive within 3–7 business days depending on location.
        </div>
      </div>

      <div class="faq-item" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; margin-top: 10px;">
        <button onclick="toggleFaq(this)" style="width: 100%; text-align: left; padding: 18px 20px; font-size: 15px; font-weight: 700; color: #111827; background: #ffffff; border: none; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
          <span>Do you handle customs duties and international import documentation?</span>
          <span class="faq-icon" style="font-size: 18px; color: #6b7280;">+</span>
        </button>
        <div class="faq-answer" style="display: none; padding: 0 20px 20px 20px; font-size: 14px; color: #4b5563; line-height: 1.6; border-top: 1px dashed #f3f4f6;">
          Yes! ImportWale provides complete commercial invoices, packing lists, HS Code details, and COO certificates required for hassle-free customs clearance.
        </div>
      </div>
    </div>

    <!-- Category 3: Payments & Invoicing -->
    <div class="faq-group" style="margin-top: 16px;">
      <h3 style="font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #059669; margin-bottom: 12px;">
        3. Payments, Currency & Invoicing
      </h3>

      <div class="faq-item" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;">
        <button onclick="toggleFaq(this)" style="width: 100%; text-align: left; padding: 18px 20px; font-size: 15px; font-weight: 700; color: #111827; background: #ffffff; border: none; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
          <span>What payment methods are supported?</span>
          <span class="faq-icon" style="font-size: 18px; color: #6b7280;">+</span>
        </button>
        <div class="faq-answer" style="display: none; padding: 0 20px 20px 20px; font-size: 14px; color: #4b5563; line-height: 1.6; border-top: 1px dashed #f3f4f6;">
          We accept Credit/Debit Cards (Visa, Mastercard), UPI, Razorpay, Wire Transfer / NEFT, and Cash on Delivery (COD) for eligible domestic orders. You can also switch your display currency anytime using the top currency selector (USD, INR, GBP, EUR, AUD, CAD).
        </div>
      </div>

      <div class="faq-item" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; margin-top: 10px;">
        <button onclick="toggleFaq(this)" style="width: 100%; text-align: left; padding: 18px 20px; font-size: 15px; font-weight: 700; color: #111827; background: #ffffff; border: none; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
          <span>Can I get a tax-compliant GST / Commercial invoice for my business?</span>
          <span class="faq-icon" style="font-size: 18px; color: #6b7280;">+</span>
        </button>
        <div class="faq-answer" style="display: none; padding: 0 20px 20px 20px; font-size: 14px; color: #4b5563; line-height: 1.6; border-top: 1px dashed #f3f4f6;">
          Absolutely. During checkout or in your Customer Account portal, provide your GSTIN or Tax Registration Number to receive a full tax credit tax invoice automatically upon order dispatch.
        </div>
      </div>
    </div>

    <!-- Category 4: Returns & Quality Claims -->
    <div class="faq-group" style="margin-top: 16px;">
      <h3 style="font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #dc2626; margin-bottom: 12px;">
        4. Quality Inspection, Returns & Replacement Claims
      </h3>

      <div class="faq-item" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;">
        <button onclick="toggleFaq(this)" style="width: 100%; text-align: left; padding: 18px 20px; font-size: 15px; font-weight: 700; color: #111827; background: #ffffff; border: none; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
          <span>What happens if an item arrives damaged or defective?</span>
          <span class="faq-icon" style="font-size: 18px; color: #6b7280;">+</span>
        </button>
        <div class="faq-answer" style="display: none; padding: 0 20px 20px 20px; font-size: 14px; color: #4b5563; line-height: 1.6; border-top: 1px dashed #f3f4f6;">
          We offer a <strong>7-Day Quality Inspection Guarantee</strong>. If any item is damaged in transit or defective, submit a photo/video proof via our <a href="<?= url('contact-us') ?>" style="color:#f05a29; font-weight:600;">Contact Support form</a>. We process instant replacement shipments or store credits within 24–48 hours.
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Still Need Help Box -->
<div style="background: #FAF4F2; border: 1px solid #f05a29; border-radius: 16px; padding: 32px; text-align: center; max-width: 900px; margin: 0 auto 40px auto;">
  <h3 style="font-size: 20px; font-weight: 800; color: #111827; margin-bottom: 8px;">Still Need Assistance?</h3>
  <p style="font-size: 14px; color: #6b7280; margin-bottom: 20px;">Our dedicated B2B Trade Specialists are available Monday to Saturday, 10:00 AM – 7:00 PM IST.</p>
  <div style="display: flex; gap: 14px; justify-content: center; flex-wrap: wrap;">
    <a href="<?= url('contact-us') ?>" style="background: #f05a29; color: #ffffff; font-weight: 700; font-size: 14px; padding: 12px 24px; border-radius: 10px; text-decoration: none; transition: background 0.2s;">
      Submit Support Ticket
    </a>
    <a href="tel:+919217714452" style="background: #ffffff; color: #111827; border: 1px solid #d1d5db; font-weight: 700; font-size: 14px; padding: 12px 24px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
      📞 Call +91 92177 14452
    </a>
  </div>
</div>

<script>
function toggleFaq(btn) {
  const answer = btn.nextElementSibling;
  const icon = btn.querySelector('.faq-icon');
  if (answer.style.display === 'block') {
    answer.style.display = 'none';
    icon.textContent = '+';
    btn.style.background = '#ffffff';
  } else {
    answer.style.display = 'block';
    icon.textContent = '−';
    btn.style.background = '#fff8f6';
  }
}

function filterFaqs() {
  const query = document.getElementById('faqSearchInput').value.toLowerCase().trim();
  const items = document.querySelectorAll('.faq-item');
  items.forEach(item => {
    const text = item.textContent.toLowerCase();
    if (!query || text.includes(query)) {
      item.style.display = 'block';
    } else {
      item.style.display = 'none';
    }
  });
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
