# Creation

## Configuration
Used mpower hernia files for most

## Data
```
cat \
mpower_tb_study_specific_tables.sql \
commonData.sql | mysql mpower_tb_dev
```

Data is seeded from mpower demo deployment, but lives in files specific to mpower_tb

## Temporary tunnel

Tunnel from erbium to ayelu
[example](https://blog.trackets.com/2014/05/17/ssh-tunnel-local-and-remote-port-forwarding-explained-with-examples.html)

```
# check for running tunnel
sudo netstat -tulpn | grep 3000

export REMOTE_HOST='ayelu.cirg.washington.edu'
export REMOTE_PORT=3306
export LOCAL_PORT=3000

# run in background (-fN)
ssh -L "$LOCAL_PORT:localhost:$REMOTE_PORT" "$REMOTE_HOST" -fN

# alternative syntax- run in foreground
#ssh -L "$LOCAL_PORT:localhost:$REMOTE_PORT" "$REMOTE_HOST"

# on erbium, connecting to ayelu, through above tunnel
mysql --port "$LOCAL_PORT" --host 127.0.0.1
```


