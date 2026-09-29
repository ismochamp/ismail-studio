# Ismail Studio — A Service Website with a Working Inquiry Desk

**Category:** Web Development  
**Project type:** New independent business asset for Ismail Habib, created in September 2026  
**Status:** Implemented and verified locally in real WordPress; not publicly deployed

## The problem

A service website needs to explain a freelancer’s capabilities clearly and carry an inquiry all the way from a visitor’s message to a usable staff record. A polished contact screen alone does not complete that workflow.

## The implementation

Built an original responsive WordPress theme with service pages, a direct contact flow and an editable content structure. A custom plugin validates inquiries, stores them in WordPress’s database and gives administrators an inbox with review status, CSV export and deletion. The design uses an ivory background, deep green typography, restrained lime accents and a clearly labeled workflow illustration. There are no stock client stories or fabricated testimonials.

Also supplied a separate Python/SQLite runtime for easy local use, plus source code, installable WordPress ZIPs and a repeatable Docker setup.

## Evidence and results

The real WordPress implementation was installed and activated on WordPress 7.1.2. Verification exercised the contact form, nonce rejection, database persistence, authenticated inbox, review status, CSV export and deletion. The standalone implementation passed four integration tests covering successful and rejected input, persistence, output escaping, export, review and deletion. Tests used clearly labeled synthetic inquiries and removed them.

## Boundaries

The form stores inquiries; it does not send email. The project has no measured sales/conversion claim, customer traffic or paid-client attribution. The Docker environment is local only. Public hosting needs operator-specific privacy information, retention rules and deployment hardening.

## Suggested screenshots

1. Homepage: service positioning, illustration and three capability cards.
2. Contact page: accessible form and clear storage explanation.
3. WordPress Inquiry desk: a clearly labeled test inquiry showing actual persisted storage.
4. Narrow-screen homepage: responsive layout.

A populated inbox screenshot must label the record as test input, not a genuine customer inquiry.
