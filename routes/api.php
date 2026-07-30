
// Internal Colyseus API (Mock for now, needs proper secret key auth)
Route::prefix("internal/colyseus")->group(function () {
    Route::get("/init", [\App\Http\Controllers\Game\InternalColyseusController::class, 'init']);
    
    Route::post("/questions", function (\Illuminate\Http\Request $request) {
        $papan_id = $request->input("papan_id");
        $posisi = $request->input("posisi");
        // Dummy mock question for MVP
        return response()->json([
            "id" => 1,
            "pertanyaan" => "Apa ibukota Indonesia?",
            "opsi_jawaban" => ["Jakarta", "Bandung", "Surabaya", "Semarang"],
            "kunci_jawaban" => "Jakarta",
            "pembahasan" => "Jakarta adalah ibukota negara Indonesia."
        ]);
    });
    
    Route::post("/finish", function (\Illuminate\Http\Request $request) {
        $winner_id = $request->input("winner_id");
        $session_id = $request->input("session_id");
        // Logic to update GameSession status to finished
        return response()->json(["status" => "success"]);
    });
});
