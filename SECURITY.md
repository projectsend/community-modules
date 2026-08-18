# Security Policy

This package is part of ProjectSend and shares its security policy.

**Report a vulnerability** through
[GitHub's private vulnerability reporting](https://github.com/projectsend/community-modules/security/advisories/new)
on this repository, or email <contact@projectsend.org>. Please do not open a public issue.

The full policy — what helps in a report, what to expect, what is in scope — is at
[projectsend/projectsend `SECURITY.md`](https://github.com/projectsend/projectsend/blob/main/SECURITY.md).

Worth naming for this package in particular: Custom Assets exists to inject operator-supplied CSS
and JavaScript into an installation, which is why it is community-only — a hosted, multi-tenant
installation cannot safely offer it. A way for someone *without* that permission to get code onto a
page is a report worth making.
