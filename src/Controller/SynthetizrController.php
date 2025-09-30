<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\RateLimiter\Attribute\RateLimiter;

class SynthetizrController extends AbstractController
{
    private const ANONYMOUS_USAGE_LIMIT = 3;

    #[Route('/api/synthetizr', name: 'api_synthetizr', methods: ['POST'])]
    #[RateLimiter('api_limiter')]
    public function synthesize(Request $request /*, Vos autres services... */): JsonResponse
    {
        // Si l'utilisateur est authentifié via le JWT, la logique de limitation ne s'applique pas.
        if ($this->getUser()) {
            // L'utilisateur est membre, on passe directement à la synthèse.
            // Le RateLimiter gère déjà les abus potentiels.
        } else {
            // L'utilisateur est anonyme. On applique la limitation par session.
            $session = $request->getSession();
            $usageCount = $session->get('anonymous_usage_count', 0);

            if ($usageCount >= self::ANONYMOUS_USAGE_LIMIT) {
                return new JsonResponse(
                    ['message' => 'Vous avez atteint votre limite de ' . self::ANONYMOUS_USAGE_LIMIT . ' synthèses gratuites. Veuillez vous inscrire pour continuer.'],
                    Response::HTTP_TOO_MANY_REQUESTS // Code 429
                );
            }

            // L'usage est autorisé, on incrémente le compteur pour la prochaine requête.
            $session->set('anonymous_usage_count', $usageCount + 1);
        }

        // --- DÉBUT DE VOTRE LOGIQUE DE SYNTHÈSE EXISTANTE ---

        // ...
        // Votre code qui récupère le texte, appelle l'IA, etc.
        // ...
        $votreSynthese = "Ceci est le résumé du texte."; // Exemple de résultat

        // --- FIN DE VOTRE LOGIQUE DE SYNTHÈSE ---

        return new JsonResponse(['summary' => $votreSynthese]);
    }
}
