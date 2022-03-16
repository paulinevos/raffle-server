# Raffle server 🎟

A cute little web socket server that can start a raffle, add some players, and pick a winner. Players join
through a **join code** set by the host and are notified if they've won when a winner is picked.

Connections should communicate through json encoded messages as specified under **Usage**.

## Requirements
- composer
- PHP ^8.1

## Usage
```shell
composer install
php ./bin/start.php
```

The server is now running on `localhost:8080`. 

### Start a raffle

1. Connect to the server using a web socket client. To try it out, open a browser tab, and in the console type:
    ```javascript
    ws = new WebSocket('ws://localhost:8080')
    ```
   You can now start sending messages to the server with `ws.send()`

2. Register as a host by sending a `registerHost` message. This will start the raffle pool. The join code provided is
the code that joining players will have to send.

    ```json
    {"message":  "registerHost", "joinCode":  "1234"}
    ```
3. Players can join the pool by connecting to the server (see: step 1), and sending a `registerPlayer` message: 
    ```json
    {"message":  "registerPlayer", "username": "Veg McCabbage", "joinCode":  "1234"}
    ```
4. Pick a winner by sending a `pickWinner` message:
    ```json
    {"message":  "pickWinner"}
    ```
   Players will then be notified about their win (`You won!`) or loss (`Better luck next time...`). The host will also
   be notified of the winner. 
5. The pool will remain open to pick more winners, as long as the host is connected and the pool isn't timed out.

### Further usage notes
- Messages sent from the raffle server to clients will always be json encoded and will have either a `message` or an 
`error` property.
- One pool may be active at any given time (may change in future versions). To start a new pool, the current host must 
  first disconnect.
- The pool will be closed automatically when the host disconnects or when it times out (defaults to 1 hour).
  Players will automatically be disconnected as well.
- Usernames must be unique per pool so the host may be notified. 
- In future versions of this raffle server, you'll be able to override defaults (e.g. port, win/loss message, timeout,
max pool size, etc.).