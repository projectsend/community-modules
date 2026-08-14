# ProjectSend — community features

This repository holds a set of features that are part of **ProjectSend**, the self-hosted
application for sharing files with your clients.

**The application itself lives at [projectsend/projectsend](https://github.com/projectsend/projectsend).**
If you are looking to install or use ProjectSend, start there — not here.

## What's in here

### Custom assets

Add your own HTML, CSS or JavaScript to your ProjectSend pages, from the admin area, without
editing any files on your server.

People use it to:

- add website analytics, or a cookie notice
- drop in a live-chat or support widget
- restyle the pages in their own colours and fonts
- put a short announcement in front of their clients

For each piece of code you add, you choose three things:

- **What it is** — HTML, CSS or JavaScript.
- **Where it goes** — in the page header, at the top of the page, or right at the bottom.
- **Who sees it** — visitors to your public pages, your clients once they sign in, your own staff
  in the admin area, or any combination of the three.

Anything you add can be switched off without deleting it, so you can turn something on for a
week and turn it off again afterwards.

You'll find it in the admin area under **Settings → Custom assets**. Only staff you've given
permission to manage them will see the screen at all.

## Installing

**There is nothing to install.** These features come with ProjectSend, and they are ready to use
the moment it is running.

- **Docker** — nothing to do. The official image already includes this.
- **Manual install from a release zip** — nothing to do. The zip already includes this.

Follow ProjectSend's own
[installation instructions](https://github.com/projectsend/projectsend#getting-started), and the
**Custom assets** screen will be waiting in the admin area.

The only time this repository matters on its own is if you are building ProjectSend from source,
in which case it is fetched automatically as one of its dependencies — you do not need to clone
it yourself.

## Contributing

Bug reports and pull requests are welcome. Development setup, the checks a change has to pass,
and the contributor agreement are all documented in ProjectSend's
[CONTRIBUTING guide](https://github.com/projectsend/projectsend/blob/main/CONTRIBUTING.md), and
apply here too.

## License

Free software under the **GNU General Public License v2, or (at your option) any later
version** — see [LICENSE](LICENSE). The same terms as ProjectSend itself.

Contributions require signing a CLA: [CLA-INDIVIDUAL.md](CLA-INDIVIDUAL.md) if you're
contributing for yourself, or [CLA-ENTITY.md](CLA-ENTITY.md) if you're doing it on behalf of an
employer.
