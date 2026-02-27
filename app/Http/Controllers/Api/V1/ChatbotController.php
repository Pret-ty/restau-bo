<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plat;
use App\Models\Boisson;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    public function recommend(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'restaurant_id' => 'nullable|integer|exists:restaurants,id',
        ]);

        try {
            /** 1️⃣ Récupération du menu **/
            $queryPlats = Plat::with('categorie');
            $queryBoissons = Boisson::query();

            if ($request->filled('restaurant_id')) {
                $queryPlats->whereHas('categorie', function ($q) use ($request) {
                    $q->where('restaurant_id', $request->restaurant_id);
                });
                $queryBoissons->where('restaurant_id', $request->restaurant_id);
            }

            $plats = $queryPlats->get(['id', 'nom', 'prix', 'categorie_id']);
            $boissons = $queryBoissons->get(['id', 'nom', 'prix', 'restaurant_id']);

            /** 2️⃣ Contexte menu **/
            $menuContext = "PLATS :\n";
            foreach ($plats as $plat) {
                // Determine restaurant ID from category for plats
                $restId = $request->restaurant_id ?? $plat->categorie->restaurant_id;
                $menuContext .= "- ID: {$plat->id}, Nom: {$plat->nom}, Prix: {$plat->prix} FCFA, RestaurantID: {$restId}\n";
            }

            $menuContext .= "\nBOISSONS :\n";
            foreach ($boissons as $boisson) {
                $restId = $request->restaurant_id ?? $boisson->restaurant_id;
                $menuContext .= "- ID: {$boisson->id}, Nom: {$boisson->nom}, Prix: {$boisson->prix} FCFA, RestaurantID: {$restId}\n";
            }

            /** 3️⃣ Prompt **/
            $prompt = <<<PROMPT
Tu es un assistant de restaurant professionnel.

Objectif :
Proposer un menu (plats + boissons) qui respecte STRICTEMENT le budget du client.

Règles :
1. Respecte le budget total.
2. 1 plat par personne (sauf budget très serré).
3. Utilise UNIQUEMENT les plats et boissons fournis.
4. Réponds UNIQUEMENT en JSON strict (sans ```json).

Format JSON attendu :
{
  "summary": "Résumé",
  "menu": {
    "plats": [
      { "id": 1, "restaurant_id": 1, "nom": "Plat", "quantite": 1, "prix_unitaire": 1000, "prix_total": 1000 }
    ],
    "boissons": [
      { "id": 2, "restaurant_id": 1, "nom": "Boisson", "quantite": 2, "prix_unitaire": 500, "prix_total": 1000 }
    ]
  },
  "total": 2000,
  "message": "Message client"
}

MENU DISPONIBLE :
$menuContext

MESSAGE CLIENT :
{$request->message}
PROMPT;

            $response = Http::timeout(30)
            ->retry(2, 500)
            ->post(
                'https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key=' . config('services.gemini.key'),
                [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]
            );

            if (!$response->successful()) {
                Log::error('Gemini API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return response()->json([
                    'message' => "Le service de recommandation est temporairement indisponible."
                ], 503);
            }

            /** 5️⃣ Extraction du texte **/
            $text = $response->json(
                'candidates.0.content.parts.0.text'
            );

            if (!$text) {
                throw new \Exception("Réponse Gemini vide.");
            }

            // Nettoyage sécurité
            $text = str_replace(['```json', '```'], '', $text);

            $json = json_decode($text, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'message' => "Impossible de générer un menu valide.",
                    'raw_response' => $text
                ], 422);
            }

            return response()->json($json);

        } catch (\Throwable $e) {
            Log::error('Chatbot Error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => "Une erreur est survenue lors du traitement."
            ], 500);
        }
    }
}
