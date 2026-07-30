import { Room, Client } from "colyseus";
import { Schema, type, MapSchema } from "@colyseus/schema";

export class Player extends Schema {
  @type("number") id: number;
}

export class GameState extends Schema {
  @type({ map: Player }) players = new MapSchema<Player>();
}

export class GameRoom extends Room<GameState> {
  maxClients = 6;

  async onCreate(options: any) {
    this.setState(new GameState());

    this.onMessage("broadcast_event", (client, message) => {
      // Murni relay pesan ke klien lain agar me-loadState()
      this.broadcast("state_changed", message, { except: client });
    });
  }

  onJoin(client: Client, options: any) {
    console.log(client.sessionId, "joined room", this.roomId);
    const player = new Player();
    player.id = options.player_id || 0;
    this.state.players.set(client.sessionId, player);
  }

  onLeave(client: Client, consented: boolean) {
    console.log(client.sessionId, "left room", this.roomId);
    this.state.players.delete(client.sessionId);
  }

  onDispose() {
    console.log("room", this.roomId, "disposing...");
  }
}
