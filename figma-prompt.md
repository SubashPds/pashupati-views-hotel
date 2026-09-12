Project: Pashupati Views Hotel — Figma Design Brief

Overview
Create a clean, warm, hospitality-focused UI kit and multi-page website design for "Pashupati Views Hotel". The site blends modern minimal layout with warm earthy colors, accessible typography, and generous whitespace. Produce high-fidelity desktop and responsive tablet/mobile screens, a complete component library, and exportable assets.

Target audience
- Leisure travelers, couples, and families looking for an upscale but approachable hotel experience.

Devices & Breakpoints
- Desktop: 1440px and up (primary)
- Tablet: 768–1024px
- Mobile: 320–667px

Pages / Screens to design
- Home page: hero, value sections, featured experiences, gallery preview, testimonials, call-to-action to book or explore packages.
- Blog listing: grid of blog cards (title, date, excerpt, cover image) with pagination.
- Blog article (show): article header (title, date), optional excerpt, large cover image, and rich HTML content area that renders CKEditor output (headings, paragraphs, lists, images, blockquotes, inline links, pull-quotes, embedded video placeholder). Ensure long-form readability, line length control, and typographic scale for h1–h4, lead paragraph, body, captions.
- Admin blog form (compact admin UI): form for Title, Excerpt, Content (rich editor placeholder), Cover image upload with preview, Status selector (Draft/Published), and actions (Save, Cancel). Include form validation states and image placeholder/clear state.
- Blog card component: image, category (if any), title, excerpt, date, read more link.

Core Components / Patterns
- Typographic scale: H1, H2, H3, H4, Lead, Body, Small/Captions.
- Buttons: Primary (rounded), Secondary, Ghost, Small action buttons.
- Forms: inputs, textareas (including rich text placeholder), selects, file upload field with thumbnail preview, checkboxes, toggle.
- Navigation: top nav with site name/logo left, menu items (Home, Rooms, Packages, Blogs, Contact), mobile hamburger + slide-over menu.
- Hero: large image or gradient background with overlay, title, small description, primary CTA.
- Cards: blog card, package card, testimonial card.
- Image gallery: masonry or grid with lightbox state.
- Modal / slide-over: for mobile nav or confirm actions.
- Badges, tags, and utility chips.

> Design details for CKEditor content rendering
- The blog article content area should accept rich HTML produced by CKEditor. Create visual styles for:
  - H1–H4 headings within the article body
  - Paragraphs with comfortable line-height and max-width for readability
  - Ordered and unordered lists (nested lists styling)
  - Blockquotes with subtle left border and muted background
  - Inline code and code blocks (monospace background)
  - Full-width images and floated images with captions
  - Embedded video placeholder (16:9) and responsive embeds
  - Pull-quotes and callout boxes (info/warning)

Color Palette & Tokens
- Primary: warm gold (#CBA15D or similar)
- Accents: deep navy (#0F1724 / similar), soft brown (#856534)
- Backgrounds: off-white (#fffdf9) and neutral grays
- Text: high-contrast dark gray for body, muted gray for meta and captions
- Provide color tokens for primary, secondary, neutral, surface, and danger.

Typography
- Suggest a serif or humanist display for headings (e.g., Playfair Display or similar) and a legible sans-serif for body (e.g., Inter, System UI). Provide font sizes and line heights for each breakpoint.

Spacing & Grid
- 12-column grid for desktop (max content width 1200–1280px), 8/6-column on tablet/mobile, consistent 4/8/16px base spacing scale.

Interactions & States
- Hover, focus, active states for buttons and links
- Form validation (error, success) and disabled states
- Image upload: show upload progress, preview, clear

Accessibility
- Ensure color contrast meets WCAG AA for body text
- Focus outlines for interactive elements
- Provide semantic heading order and landmark regions

Assets & Deliverables
- Component library (Figma components / variants): Buttons, Inputs, Cards, Nav, Hero, Blog Card, Article content patterns.
- 3 responsive artboards per page (Desktop, Tablet, Mobile)
- A sample article page populated with sample CKEditor content (headings, lists, images, blockquote, embed placeholder)
- Exportable SVG logo and JPG/WEBP hero and card images

Sample content (for populating mockups)
- Site name: Pashupati Views Hotel
- Example blog title: "A Tranquil Stay: Discovering the Hills"
- Example excerpt: "A brief guide to enjoying the serene views and curated experiences at Pashupati Views Hotel."

Developer Notes
- Provide CSS tokens or a small style guide panel showing color hex values, spacing units, font stack, and exported icons.
- Indicate where cover images should be cropped vs. contain (cards: 16:9 crop; hero: full-bleed cover with focal center; article images: natural aspect with optional full-width).

Tone & Visual Direction
- Warm, inviting, and elegant — balance modern minimal UI with earthy textures and photographic hero imagery. Prioritize clarity and hospitality warmth over heavy ornamentation.

Acceptance Criteria
- All pages have responsive layouts and reusable components
- Article content area supports typical CKEditor output types (headings, lists, images, quotes, embeds)
- Deliverables include component library and exportable assets ready for frontend implementation

Notes for the designer
- If unsure about a component, create 2–3 variants and label the recommended default.
- Annotate spacing, type scales, and interactions for handoff.

---
End of brief. Use this as the prompt to feed into Figma or to give to a designer to create the UI kit and responsive pages for the Pashupati Views Hotel website.
