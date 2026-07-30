import { Schema, type, ArraySchema } from "@colyseus/schema";

export class Player extends Schema {
  @type("number") id: number;
  @type("number") user_id: number;
  @type("string") nama: string;
  @type("boolean") is_robot: boolean;
  @type("number") turn_order: number;
  @type("string") pawn_color: string;
  @type("number") posisi_pion: number = 0;
  @type("number") skor: number = 0;
  @type("string") status: string = "active";
  
  // Powerups (array of strings)
  @type(["string"]) inventory = new ArraySchema<string>();
}

export class GameState extends Schema {
  @type("string") status: string = "waiting";
  @type("number") current_turn_game_player_id: number;
  @type("number") total_turn: number = 0;
  @type("number") started_at: number;
  @type([Player]) players = new ArraySchema<Player>();
}
