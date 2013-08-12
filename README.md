Varnish helper for WordPress in purge and ESI.

To get the PURGE and BAN request worked, you should config your Varnish correctly. Take Varnish 3 as an example:
```
acl purge {
	"127.0.0.1";
}

sub vcl_recv {
	...
	if(req.request == "PURGE") {
		if (client.ip !~ purge) {
			error 405 "Not allowed";
		}
		return (looktup);
	}

	if (req.request == "BAN") {
		if (client.ip !~ purge) {
			error 405 "Not allowed";
		} else {
			ban("req.http.host == " + req.http.Host + " && req.url ~ ^" + req.url);
			error 200 "Banned";
		}
	}
	...
}

sub vcl_hit {
	if (req.request == "PURGE") {
		purge;
		error 200 "Purged";
	}
	return (deliver);
}

sub vcl_miss {
	if (req.request == "PURGE") {
		purge;
		error 200 "Not in cache";
	}
	return (fetch);
}
```
For security, limit the IPs that can be only requested PURGE and BAN.