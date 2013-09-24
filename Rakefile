task :default do
	
end

task :watch do
	pids = [
		spawn("coffee -w -o js/ -c src/js/*.coffee")
	]

	trap "INT" do
		Process.kill "INT", *pids
		exit 1
	end

	loop do
		sleep 1
	end
end