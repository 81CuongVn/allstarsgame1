#!/usr/local/rvm/rubies/ruby-2.6.5-p114/bin/ruby

require 'daemons'

module Daemons
	class Application
		def logfile;		'/var/www/allstarsgame/servers/pvp/logs/queue_log.log'; end
		def output_logfile;	'/var/www/allstarsgame/servers/pvp/logs/queue_output.log'; end
		def backtrace;		true; end
	end
end

Daemons.run '/var/www/allstarsgame/servers/pvp/queue.rb', dir: '/var/www/allstarsgame/servers/pvp', dir_mode: :normal, ontop: false, log_output: true