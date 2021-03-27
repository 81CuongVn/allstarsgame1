#!/usr/local/rvm/rubies/ruby-2.6.5-p114/bin/ruby

require 'daemons'

module Daemons
	class Application
		def logfile;		'/var/www/allstarsgame/servers/pvp/logs/queue_log.log'; end
		def output_logfile;	'/var/www/allstarsgame/servers/pvp/logs/queue_output.log'; end
		def backtrace;		true; end
	end
end

queue_file	= File.join(File.dirname(__FILE__), 'queue.prod.rb');
Daemons.run queue_file, dir: '/var/www/allstarsgame/servers/pvp', dir_mode: :normal, ontop: false, log_output: true
# Dir.mkdir('/var/www/allstarsgame/servers/pvp', 0777) rescue nil
# Daemons.run '/var/www/allstarsgame/servers/pvp/queue.prod.rb', dir: '/var/www/allstarsgame/servers/pvp', dir_mode: :normal, ontop: false, log_output: true
