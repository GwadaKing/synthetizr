<?php

namespace App\Controller;

// --- IMPORTS ESSENTIELS DE SYMFONY & DOCTRINE ---
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\InMemoryUser;

// --- IMPORTS DE LA SOLUTION RESTFUL & AUTHENTIFICATION ---
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Google\Client;
use DateTime;
use App\Security\User; // <-- IMPORTANT : L'ENTITÉ UTILISATEUR SIMPLIFIÉE

// --- IMPORTS DES ENTITÉS ---
use App\Entity\UserUsage;
use App\Entity\Synthese;

class ApiController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private TokenStorageInterface $tokenStorage;
    private HttpClientInterface $httpClient;
    private string $aiProjectId;
    private string $aiLocation;
    private string $googleCredentialsPath;

    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        HttpClientInterface $httpClient,
        string $aiProjectId,
        string $aiLocation,
        ParameterBagInterface $params 
    ) {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->httpClient = $httpClient;
        $this->aiProjectId = $aiProjectId;
        $this->aiLocation = $aiLocation;
        
        $projectDir = $params->get('kernel.project_dir');
        $this->googleCredentialsPath = $projectDir . '/config/secrets/aerial-mission-449215-j8-de0abe72ef42.json';
    }

    #[Route('/api/synthetizr', name: 'api_synthetizr', methods: ['POST'])]
    public function synthetize(Request $request): JsonResponse
    {
        // --- ÉTAPE 1 : VÉRIFICATION D'AUTHENTIFICATION ET DE VERROUILLAGE (V47) ---
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return $this->json(['message' => 'Token JWT manquant ou invalide.'], 401);
        }

        $user = $token->getUser();
        
        // VÉRIFICATION DE VERROUILLAGE PROPRE : Rejeter les utilisateurs de test (InMemoryUser)
        // et ceux dont l'objet n'est pas le type attendu (App\Security\User).
        if (!$user || !$user instanceof User) {
            return $this->json([
                'message' => 'Accès exclusif. L\'outil Synthetizr est réservé aux membres de Techmastermind.fr.',
                'action' => 'Veuillez vous inscrire ou vous connecter pour débloquer l\'outil.',
                'link' => 'https://www.techmastermind.fr/connexion',
            ], 403); // Code 403: Forbidden - Accès refusé
        }
        
        // Si la vérification passe, l'ID est bien l'ID WordPress (string)
        $userId = $user->getUserIdentifier();
        // --- FIN VÉRIFICATION V47 ---
        

        // --- DÉBUT DE LA LOGIQUE MÉTIER ---

        // Création de l'objet DateTime simple (Mutable)
        $usagePeriod = new \DateTime('first day of this month');
        $userUsageRepo = $this->entityManager->getRepository(UserUsage::class);
        
        // RECHERCHE SIMPLIFIÉE (ID non composite) : recherche simple par user_id et période
        $usageRecord = $userUsageRepo->findOneBy([
            'user_id' => (int) $userId, 
            'usage_period' => $usagePeriod 
        ]);
        
        $limit = 100;

        if ($usageRecord && $usageRecord->getRequestCount() >= $limit) {
            return $this->json(['message' => 'Limite d\'utilisation mensuelle atteinte.'], 429);
        }

        $data = json_decode($request->getContent(), true);
        $textToSummarize = $data['text'] ?? '';
        if (empty($textToSummarize)) {
            return $this->json(['message' => 'Le champ texte est manquant.'], 400);
        }

        // --- AUTHENTIFICATION GOOGLE CLIENT API (STABLE) ---
        $modelId = 'gemini-2.5-flash';
        $location = $this->aiLocation;
        $projectId = $this->aiProjectId;
        
        try {
            $client = new \Google\Client();
            $client->setAuthConfig($this->googleCredentialsPath); 
            $client->setScopes(['https://www.googleapis.com/auth/cloud-platform']);
            
            $iamToken = $client->fetchAccessTokenWithAssertion()['access_token'];

        } catch (\Throwable $e) {
            return $this->json(['message' => 'Erreur Critique d\'Authentification: ' . $e->getMessage()], 500);
        }
        
        // --- APPEL À L'API REST GEMINI ---
        $url = "https://{$location}-aiplatform.googleapis.com/v1beta1/projects/{$projectId}/locations/{$location}/publishers/google/models/{$modelId}:generateContent";

        $requestBody = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => 'Summarize the following text in French in a few sentences: ' . $textToSummarize]
                    ]
                ]
            ],
            'generation_config' => [
                'temperature' => 0.2
            ]
        ];
        
        try {
            $response = $this->httpClient->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $iamToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $requestBody,
            ]);

            $statusCode = $response->getStatusCode();
            
            if ($statusCode !== 200) {
                 $content = $response->toArray(false);
                 $errorMessage = $content['error']['message'] ?? json_encode($content); 
                 
                 return $this->json([
                     'message' => 'Erreur API Google Cloud (Status: ' . $statusCode . ')', 
                     'details' => $errorMessage
                 ], $statusCode);
            }
            
            $content = $response->toArray();
            $summary = $content['candidates'][0]['content']['parts'][0]['text'] ?? 'Synthèse non trouvée.';

        } catch (\Throwable $e) {
            return $this->json(['message' => 'Erreur lors de l\'appel HTTP: ' . $e->getMessage()], 500);
        }
        
        // --- ENREGISTREMENT ET RÉPONSE FINALE ---
        
        $synthese = new Synthese();
        $synthese->setUserId((int) $userId);
        $synthese->setRequestParams(['model' => $modelId]);
        $synthese->setResultText($summary);
        $synthese->setApiTokensUsed(mb_strlen($textToSummarize)); 
        $this->entityManager->persist($synthese);

        if (!$usageRecord) {
            $usageRecord = new UserUsage();
            $usageRecord->setUserId((int) $userId);
            
            $usageRecord->setUsagePeriod($usagePeriod); 
            
            $this->entityManager->persist($usageRecord);
        }
        $usageRecord->incrementRequestCount();

        $this->entityManager->flush();

        return $this->json(['summary' => $summary]);
    }

    #[Route('/api/login_test', name: 'api_login_test', methods: ['POST'])]
    public function loginTest(JWTTokenManagerInterface $jwtManager): JsonResponse
    {
        $user = new InMemoryUser('test_user', null, ['ROLE_USER']);

        $token = $jwtManager->create($user);

        return $this->json(['token' => $token]);
    }
}