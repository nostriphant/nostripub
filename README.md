# nostripub
Bridging ActivityPub and nostr

The goal of this app is being able to communicate with users on nostr as a ActivityPub-user or vice-versa as a nostr-user with ActivityPub-users. The main difference with [Mostr](https://mostr.pub/) is that nostripub does not implement a relay itself. It is a thin HTTP-API layer combined with a nostr client to relay messages between nostr and ActivityPub. It requires you to have a nostr-pubkey and a ActivityPub-account.

# ActivityPub -> Nostr
nostripub follows that account and any post will be forwarded to nostr (being signed by your signer-app). Posting to nostr requires a signer-app (eg Amber) to sign your events off to nostr, therefore you'll have to register your pubkey with your ActivityPub-account to follow (eg https://mastodon.social/@rik1984). 
Nostripub sends that message to preconfigured relays (you can set them up when registering your pubkey, a default list will be set based on your profile).
You can find a user by its nip-05 identifier (user@domain.tld --> /.well-known/webfinger?resource=acct:user@domain.tld), nostripub will retrieve the pubkey and other information regading this user through a nip-05 request (https://domain.tld/.well-known/nostr.json?name=user)


# Nostr -> ActivityPub
nostripub listens on pre-configured relays (again during registration or your profile list of relays). There are a few scenario's/types of messages:
- public messages from pubkeys you follow
- hashtags you follow
- private messages sent to your pubkey (NIP46 decrpyt)

# Registration and configuration
this is to be designed, but the goal is to allow registration/configuration through direct messaging with an agent

# TODO
- [] Subscribe from Mastodon to nostr pubkeys
- [] Subscribe from nostr to Mastodon accounts
- [] Follow hashtags from nostr on Mastodon
- [] Follow hashtags from Mastodon on nostr
- [] Send private dms from Mastodon to nostr
- [] Send private dms from nostr to Mastodon