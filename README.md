# OpenDXP | Google Marketing Bundle

***

## Disclaimer

> OpenDXP is a community-driven fork based on the Pimcore® Community Edition (GPLv3).  
> OpenDXP is independent and maintained by its community and contributors.
> It is not affiliated with, endorsed by, or sponsored by Pimcore GmbH.   
> Original credits: [Pimcore GmbH](https://www.pimcore.com)

**OpenDXP Google Marketing Bundle is based on the Pimcore® Community Edition and remains licensed under GPLv3.**

***

The `Marketing Settings` gives you the possibility to configure marketing-specific settings, which are:

- Google Analytics
- Google Search Console
- Google Tag Manager

### Google Analytics

Google Analytics code is automatically injected during the rendering of the page. See [Google Analytics](./docs/05_Analytics.md) for
details.

### Google Tag Manager

The Google Tag Manager code is built and injected in a similar way as the Google Analytics one and exposes the same customization
possibilities through:

* the `GoogleTagManagerEvents::CODE_HEAD` and `GoogleTagManagerEvents::CODE_BODY` events, each defining a set of customizable
  blocks
* a dedicated template for both events, which can be customized from an event listener

### Google Service Integrations
For a more detailed description, see [Google Service Integration](./docs/10_Google_Services_Integration.md)

***

## Upstream Origin & Version Transparency
This project is a fork of [Pimcore google-marketing-bundle (76666f5 / v1.1.1)](https://github.com/pimcore/google-marketing-bundle/tree/76666f52884d00092aa33a9a0e6b56493b87b1ca), which is © Pimcore GmbH and licensed under GPLv3.

## License
Licensed under the GNU General Public License v3.0 (GPLv3). For details, please see [LICENSE.md](LICENSE.md).

## Copyright
© Pimcore GmbH  
© 2025 OpenDXP Contributors — GPLv3

## Trademarks
Pimcore® is a registered [trademark](https://www.trademarkelite.com/europe/trademark/trademark-detail/009309841/PIMCORE) of Pimcore GmbH.
Any use of the Pimcore® mark in this repository is purely descriptive to identify the original upstream project.

***

## Contact
For inquiries, suggestions, or contributions, feel free to reach us at contact@opendxp.io.

## About
OpenDXP is a community-driven project initiated by [DACHCOM.DIGITAL](https://www.dachcom.com/de-ch) (Rheineck, Switzerland) and maintained by its community and contributors.
OpenDXP is independent and not affiliated with Pimcore GmbH.

The project’s purpose is to preserve and maintain a GPLv3‑licensed codebase for community use.

It is **not positioned as a competitor** to products or services of Pimcore GmbH and does **not** purport to replace or supersede any Pimcore offering.   
