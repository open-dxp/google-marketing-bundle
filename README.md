# OpenDXP | Google Marketing Bundle

***

## Disclaimer

> The open future of Pimcore CE. A community-driven fork based on Pimcore Community Edition (GPLv3), created to keep Open Source open!  
> Original credits: [Pimcore GmbH](https://www.pimcore.com)

OpenDXP Google Marketing Bundle is an independent, community-maintained fork of Pimcore Google Marketing Bundle.
It is not affiliated with or endorsed by Pimcore GmbH.

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

## Copyright and License
Copyright: OpenDXP

This project is a fork of [Pimcore google-marketing-bundle (76666f5 / v1.1.1)](https://github.com/pimcore/google-marketing-bundle/tree/76666f52884d00092aa33a9a0e6b56493b87b1ca),
which is © Pimcore GmbH and licensed under the GPLv3.

For licensing details please visit [LICENSE.md](LICENSE.md)

***

## Contact
For inquiries, suggestions, or contributions, feel free to reach us at contact@opendxp.ch.

## About
OpenDXP is a community-driven project maintained and developed by [DACHCOM.DIGITAL](https://www.dachcom.com/de-ch), based in Rheineck, Switzerland.
