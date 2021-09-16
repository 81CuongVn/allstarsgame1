# Anime All-Stars Game
Este é o repositório oficial do https://allstarsgae.com.br!

## Pré-requisitos
- PHP 7.2+ (Curl, GD, Mbstring, OpenSSL, Redis, XML)
- Ruby 2.5.6 (Gems: bunny, mysql2, activesupport)
- NodeJS 12+ (Yarn, PM2, CoffeeScript)
- Erlang
- RabbitMQ
- Redis Server

## Permissões
As seguintes pastas precisam ter suas permissões(776)/grupo de usuário configurados(de acordo com o usuário/grupo do servidor web) apropriadamente:

- cache/recset
- cache/store
- cache/yaml

- logs
- logs/battles/npc
- logs/battles/pvp
- logs/paypal

- uploads/guilds
- uploads/support

## Instalação da crontab
A crontab pode ser inserida diretamente no arquivo **crontab.txt**

## Dependências
Em caso de novo servidor, ter instalado o composer.

Entrar na pasta public e executar o comando:

```shell
composer install
```

> ***Nunca se deve copiar a pasta "vendor" localizada na pasta public. Ela sempre deve ser instalada pois dependencias nativas podem quebrar.***
> ***Nunca comitar a pasta "vendor" localizada na pasta public de forma forçada. Não é boa prática e não faz sentido armazenar dados de depenências.***

## Serviços em NodeJS
Em caso de novo servidor, ter instalado o NodeJS 12 ou superior e o yarn(preferencialmente)/npm.

Entrar em node/chat e node/highlights e executar o:

```shell
yarn
```

> ***Nunca se deve copiar a pasta "node_modules" dos serviços em node. Ela sempre deve ser instalada pois dependencias nativas podem quebrar.***

> ***Nunca comitar a pasta node_modules dos serviços de forma forçada. Não é boa prática e não faz sentido armazenar dados de depenências.***
