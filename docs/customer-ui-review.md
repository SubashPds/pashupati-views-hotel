# Customer UI review

Reviewed the local site at desktop, tablet, and mobile widths (1440, 768, 375, and 360px). Browsed the homepage, room/package cards, contact section, and booking dialog without submitting an enquiry. Findings reflect the current local content, not a deployed-site audit.

1. **Replace placeholder contact details and test content before launch.** The visible phone number is `+977-1-XXXXXXX`; an active room is named “test”; the package and one testimonial contain sample text. Guests need working contact information and credible accommodation descriptions before they can make a decision. Review these entries in Site Settings, Rooms, Packages, and Testimonials.
2. **Repair missing gallery photos.** Five gallery image URLs failed to load: temple, garden, dining, suite, and valley-view images. The fallback tiles keep the layout intact but cannot help guests assess the hotel. Upload real photographs and remove unavailable gallery entries.
3. **Let guests see the hotel before the offer popup.** The promotional dialog opens over the first screen on both desktop and mobile, obscuring the introduction and the country/currency prompt. Consider a delayed or visitor-triggered offer instead of an immediate interruption.
4. **Make the booking promise clearer.** “Book Now” and “Book this room” open a form that sends an enquiry and promises a later availability confirmation. Label this action “Enquire about this room” or “Request availability,” or state clearly before the form that it does not confirm a reservation.
5. **Explain contact requirements and show errors beside the relevant fields.** Both phone and email look optional, while submission requires at least one. Add “Provide a phone number or email” before the fields. The contact form has no inline email error, and the booking dialog does not display server validation errors or restore entered values. These gaps can leave guests unsure why submission failed.

The narrower section spacing, clear currency codes, room specifications, photo controls, and mobile Call / WhatsApp / Book Now bar help guests browse. No page-wide horizontal overflow was observed at the tested widths, and no JavaScript exceptions occurred in this browsing check. Map availability and actual email delivery were not verified.

This review records recommendations; it does not change content or booking behavior.
