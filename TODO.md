# ClickIt-URL-Shortener

A simple, but featureful, URL shortening service, for self-hosted usage.

### To Do

- [x] **NB**:bangbang:: [Matomo](#matomo) Tracking!
  - [ ] Confirm this is working
- [ ] Documentation on how to use the shorts, specifically what '-' & '@' does to the end of the short.
- [ ] Documentation on the format of `short_urls.json`.
- [ ] More plugins:
  - [ ] [Wifi QR-Code](#wifi)

### In Progress

- [ ] Allow URL's to expire // needs testing

### Done &#x2713;

- [x] Release v2.0.0
- [x] Plugin system
- [x] Documentation on the plugin methodology.
- [x] More plugins:
  - [x] [Email](#email)
  - [x] [SMS](#sms)

### WiFi

**Format:** "WIFI:T:WPA;S:MyNetwork;P:password;;"

QR code examples:
- "WIFI:S:WIFIID;T:WPA;P:PASSWORD;;"  // WPA/WPA2
- "WIFI:S:WIFIID;T:WEP;P:PASSWORD;;"  // WEP
- "WIFI:S:WIFIID;T:nopass;P:PASSWORD;;"  // No encryption
- "WIFI:S:THE WIFI ID;T:WPA;P:PASSWORD;;"

### EMail

QR code examples (same as for URL encoding):
- "mailto:name@mail.com?subject=Subject Subject&body=Message message message message message message"  // shape like URL

### SMS

QR Code examples:
- "SMSTO:+610490490490:Message message message message message"  // shape like URL

URL code examples:
- "sms:+61412345678&body=Hello%20there"
