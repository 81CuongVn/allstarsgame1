#!/usr/local/rvm/rubies/ruby-2.6.5-p114/bin/ruby

require 'daemons'

module Daemons
	class Application
		def logfile;		'/var/www/animeallstarsgame.com.br/servers/pvp/logs/logfile.log'; end
		def output_logfile;	'/var/www/animeallstarsgame.com.br/servers/pvp/logs/output_logfile.log'; end
	end
end

Daemons.run '/var/www/animeallstarsgame.com.br/servers/pvp/queue.rb', dir: '/var/www/animeallstarsgame.com.br/servers/pvp', dir_mode: :normal, ontop: false, log_output: true