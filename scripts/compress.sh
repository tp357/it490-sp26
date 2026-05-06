tar -cv $1 -f $2
sftp phoenix@phoenix-0:/home/phoenix/$3 <<< $2
