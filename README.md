# Anime All-Stars Game
Este é o repositório oficial do https://allstarsgame.com.br

## Pré-requisitos
- PHP 7.2+ (*Curl, GD, Mbstring, OpenSSL, Redis and XML*)
- Ruby 2.5.6 (*Gems: bunny, mysql2, activesupport and daemons*)
- NodeJS 12+ (*Yarn and PM2*)
- Redis Server
- Erlang + RabbitMQ

## Permissões
As seguintes pastas precisam ter suas permissões(776)/grupo de usuário configurados(de acordo com o usuário/grupo do servidor web) apropriadamente:

- public/cache/recset
- public/cache/store
- public/cache/yaml
- public/logs
- public/logs/battles/npc
- public/logs/battles/pvp
- public/logs/paypal
- public/uploads/guilds
- public/uploads/support

## Dependências
Em caso de novo servidor, ter instalado o composer.
Entrar na pasta public e executar o comando:

```shell
composer install --ignore-platform-reqs
```

> ***Nunca se deve copiar a pasta "vendor" localizada na pasta public. Ela sempre deve ser instalada pois dependencias nativas podem quebrar.***

> ***Nunca comitar a pasta "vendor" localizada na pasta public de forma forçada. Não é boa prática e não faz sentido armazenar dados de depenências.***

## Serviços em NodeJS
Em caso de novo servidor, ter instalado o NodeJS 12 ou superior e o yarn(preferencialmente)/npm.
Entrar em servers/chat e servers/highlights e executar o comando:

```shell
yarn
```

> ***Nunca se deve copiar a pasta "node_modules" dos serviços em node. Ela sempre deve ser instalada pois dependencias nativas podem quebrar.***

> ***Nunca comitar a pasta node_modules dos serviços de forma forçada. Não é boa prática e não faz sentido armazenar dados de depenências.***

## Iniciando serviços
Para iniciar os serviços do **Chat**, **Highlights** e **PvP Queue**, use o os comandos abaixo
```shell
pm2 start path-to-folder/allstarsgame/servers/chat/index.js --name aasg-chat
pm2 start path-to-folder/allstarsgame/servers/highlights/index.js --name aasg-alerts

ruby path-to-folder/allstarsgame/servers/pvp/service.rb start
```