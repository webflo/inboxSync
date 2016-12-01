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
  - Update the `config/config.yml`

Run:

```
$ ./inboxSync configure
```

Grant access to your account via the opened browser-window and copy
the displayed code and paste it back into your terminal.


Run:

```
$ ./inboxSync github:sync
```

Start reading your github notifications and see it getting synced.
