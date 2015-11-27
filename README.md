# Inbox Sync

Sync the read state of issue and notifications between Goolge Inbox/Gmail and GitHub.

## Installation

```
mkdir -p config
cp config.yml.dist config/config.yml
```

## Google Account

- Create a new project https://console.developers.google.com/home/dashboard
- Create new API credentials on https://console.developers.google.com/apis/credentials
  - Anwendungstyp: Sonstiges
  - Name: InboxSync CLI
  - Download the OAuth config
