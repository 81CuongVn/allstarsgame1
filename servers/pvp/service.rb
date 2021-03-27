#!/usr/local/rvm/rubies/ruby-2.6.5-p114/bin/ruby

require 'daemons'

module Daemons
	class Application
		def logfile;		'/var/www/allstarsgame/logs/pvp_queue.log'; end
		def output_logfile;	'/var/www/allstarsgame/logs/pvp_queue2.log'; end
	end
end

Dir.mkdir('/var/run/allstars', 0777) rescue nil
Daemons.run '/var/www/allstarsgame/queue.prod.rb', dir: '/var/run/allstars', dir_mode: :normal, ontop: false, log_output: true
