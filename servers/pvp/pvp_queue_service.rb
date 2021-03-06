#!/usr/local/rvm/rubies/ruby-2.0.0-p643/bin/ruby

require 'daemons'

module Daemons
	class Application
		def logfile;		'/home/anime/www/crons/background/log/pvp_queue.log'; end
		def output_logfile;	'/home/anime/www/crons/background/log/pvp_queue2.log'; end
	end
end

Dir.mkdir('/var/run/aasg', 0777) rescue nil
Daemons.run '/home/anime/www/crons/background/pvp_queue_v2.rb', dir: '/var/run/aasg', dir_mode: :normal, ontop: false, log_output: true
