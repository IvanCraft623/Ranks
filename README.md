# Ranks
[![](https://poggit.pmmp.io/shield.state/Ranks)](https://poggit.pmmp.io/p/Ranks)
[![](https://poggit.pmmp.io/shield.api/Ranks)](https://poggit.pmmp.io/p/Ranks)
[![](https://poggit.pmmp.io/shield.dl.total/Ranks)](https://poggit.pmmp.io/p/Ranks)

[Download](https://poggit.pmmp.io/r/112465/Ranks.phar)

Ranks by IvanCraft623 is a plugin that with conjunction of [PurePerms](https://github.com/poggit-orphanage/PurePerms/) allow set Temporal Ranks to players.
It is necessary have the [PurePerms](https://github.com/poggit-orphanage/PurePerms/) plugin!

# Commands
Command | Description | Permission
--- | --- | ---
`/ranks settemprank <player> <rank> <time in days>` | Set a TempRank to a player. | ranks.cmd.settemprank
`/ranks createcode <code> <rank> <max uses> <time in days>` | Create a code to claim a rank. | ranks.cmd.createcode
`/ranks deletecode <code>` | Delete a code. | ranks.cmd.deletecode
`/ranks manage` | Open an UI to manage. | ranks.cmd.manage
`/ranks claim` | Open an UI to claim a code | None

# Config

``` YAML

#Ranks by IvanCraft623 (Twitter: @IvanCraft623)

#Do not edit this value, only internal use
Config Version: 1.0.0

#Behaviour when a rank expires
# - setdefaultrank: When a rank expires the player will be set to the default rank
# - setlastrank: When a rank expires the player will be given the rank he had previously
mode: setlastrank

#Custom messages coming soon... :D
```

# Features

- You can set Temporal Ranks to players!
- You can add codes to claim temporal ranks!
- If when you are creating a code you set code "random" a random code will be generated
