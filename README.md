# LunarCovenant

![LunarCovenant Logo](https://github.com/ChristianGibilaro/TCH099-project-de-session/blob/web_deployed/ressources/Final/Main/logo.png?raw=true)

## 🚀 Introduction

**LunarCovenant** is an innovative project aimed at bridging the gap between users seeking teammates for both virtual and real-world activities. It encompasses a **web platform** and an **Android application**, offering a seamless solution for short-term and long-term collaborations. Inspired by the community-driven model of platforms like Reddit, users will generate and moderate content on the platform, ensuring an engaging, user-centric experience.

The platform is designed to accommodate any activity that fosters interaction, from casual gaming to sports or professional team-building. While the website will provide comprehensive features, the Android app will focus on delivering the most essential functionalities.

Visit the final site at [LunarCovenant.com](https://lunarcovenant.com).

---

## 📖 Table of Contents

1. [Features](#features)
2. [Installation](#installation)
3. [Usage](#usage)
4. [Contributing](#contributing)
5. [License](#license)

---

## ✨ Features

- **User Profiles**: Create and manage personal profiles, including experience and activity history.
- **Team Formation**: Create or join teams for activities, with moderation options for team leaders.
- **Activity Pages**: Dedicated pages for activities with associated teams and moderation tools.
- **Content Moderation**: Both user-driven and automated moderation (AI feasibility under evaluation).
- **Advanced Search**: Filter by activity type, required skill levels, and availability.
- **Chat System**: Basic communication tools to connect with teammates.

---

## 🛠️ Installation

Clone the repository and configure the desired branch for each component.

### Android Application
```bash
# Clone the repository
git clone -b android https://github.com/ChristianGibilaro/TCH099-project-de-session.git LunarCovenant-Android

# Navigate to the app directory
cd LunarCovenant-Android

# Install dependencies and build the app
# Example for Gradle:
./gradlew build
```

### Backend
```bash
# Clone the repository
git clone -b backend https://github.com/ChristianGibilaro/TCH099-project-de-session.git LunarCovenant-Backend

# Navigate to the backend directory
cd LunarCovenant-Backend

# Set up the backend server
# Example for Node.js:
npm install
npm start
```

### Website
#### Beta Version
```bash
# Clone the repository
git clone -b web https://github.com/ChristianGibilaro/TCH099-project-de-session.git LunarCovenant-Web-Beta

# Navigate to the web directory
cd LunarCovenant-Web-Beta

# Install dependencies and run the development server
npm install
npm run dev
```

#### Stable Version
```bash
# Clone the repository
git clone -b web_deployed https://github.com/ChristianGibilaro/TCH099-project-de-session.git LunarCovenant-Web-Stable

# Navigate to the deployed web directory
cd LunarCovenant-Web-Stable

# Install dependencies
npm install

# Serve the stable version
npm run start
```

---

## 🤝 Contributing

We welcome contributions! Follow these steps:

1. Fork the repository.
2. Create a branch for your feature: `git checkout -b feature-name`.
3. Commit your changes: `git commit -m 'Add feature name'`.
4. Push to the branch: `git push origin feature-name`.
5. Open a pull request.

---

## 📜 License

This project is licensed under the [MIT License](LICENSE).

<style>
* {
  font-family: Arial, sans-serif;
}
</style>
