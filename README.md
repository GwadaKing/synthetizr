# Synthetizr 🧠 | Synthétiseur de Connaissances par IA

## 🌟 Statut du Projet

**Alpha Publique**

Ce projet sert de banc d'essai pour une architecture Headless moderne couplée à des services d'Intelligence Artificielle d'entreprise. Nous recherchons des retours critiques sur la performance, la stabilité du backend, et la fluidité de l'expérience utilisateur.

---

## 💡 Présentation et Justification Technique

**Synthetizr** est un outil de synthèse de texte conçu pour être ultrarapide et économe en ressources.

**Notre Philosophie :** L'échec n'est pas d'avoir trouvé l'API Vertex AI instable (cf. notre post-mortem public sur LinkedIn), mais d'avoir abandonné. Ce projet est le résultat d'un pivot : nous avons **rejeté le SDK PHP officiel** pour construire une **couche d'abstraction purement REST** en Symfony, ce qui est l'unique solution viable pour une intégration rigoureuse en PHP.

### ⚙️ Stack Technique

| Rôle | Technologie | Justification |
| :--- | :--- | :--- |
| **Backend / API** | **Symfony 7** | Robustesse, sécurité, gestion des dépendances (Headless). |
| **Frontend / UX** | **Svelte.js** | Performance maximale, bundle léger, choix axé sur la vitesse d'exécution. |
| **IA Engine** | **Vertex AI / Gemini (via API REST)** | Choix stratégique pour la fiabilité Cloud et l'accès aux modèles d'entreprise. |

---

## 🚀 Démarrage Rapide

Ce projet nécessite deux environnements distincts (Backend Symfony et Frontend Svelte).

### Prérequis

* PHP 8.2+
* Composer
* Node.js & npm/Yarn

### 1. Configuration de l'Environnement

Créez le fichier **`.env.local`** (ou `.env.dev.local`) à la racine du projet (Backend Symfony). Copiez-y vos identifiants réels.

> **CRITIQUE SÉCURITÉ :** Le fichier **`.env.local`** est protégé par `.gitignore` et ne doit jamais être committé !

### 2. Backend Symfony (API)

```bash
# 1. Installez les dépendances Symfony
composer install

# 2. Sécurité : Assurez-vous que le .env.local est en place (secrets)

# 3. Démarrez le serveur de développement (ou utilisez le serveur prod)
symfony server:start
```

### 🔒 Sécurité et Clés API
AVERTISSEMENT MAJEUR : Ce dépôt contient les chemins vers des fichiers secrets et des configurations sensibles.

Les identifiants d'API (Vertex AI, JWT Passphrase) DOIVENT être stockés dans des fichiers ignorés par Git (.env.local ou Secrets Symfony).

L'authentification Google Cloud repose sur le fichier de clé de service (.json) protégé par notre règle .gitignore : /config/secrets/*.

### 🤝 Contribuer et Feedback
Si vous rencontrez des problèmes de performance, de latence ou des bugs liés à la couche d'abstraction REST, veuillez ouvrir une "Issue" sur ce dépôt. Les retours sur la qualité du code et l'architecture Svelte/Symfony sont les bienvenus.

