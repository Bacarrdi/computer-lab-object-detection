# Computer Lab Object Detection

**Computer vision prototype for detecting and counting objects in university computer laboratories using YOLOv8s, Laravel, Python, and Docker.**

## About the Project

Computer Lab Object Detection is an academic project originally developed in **2025 by a team of four university students**.

It was our first practical experience building an object detection application using a custom-trained computer vision model.

The application allows users to upload photographs of computer laboratories, identify objects, compare automatic and manual counts, and explore statistical summaries.

In **2026**, the original project underwent partial refactoring and maintenance to improve code organization, preserve the core functionality, simplify the interface, and provide a Docker-based development environment.

This repository documents the project as a learning experience rather than a production-ready system.

## Technologies

- **Python 3.11** — object detection and statistical processing
- **YOLOv8s / Ultralytics** — custom-trained object detection model
- **OpenCV** — image processing and bounding-box visualization
- **Pandas and Matplotlib** — statistical analysis and charts
- **Laravel 12 / PHP 8.3** — application backend and web interface
- **JavaScript / Web Speech API** — voice-assisted navigation
- **Docker Compose** — local development environment

## Application Modules

The application is organized into four modules, each focusing on a different aspect of object detection, interaction, or analysis.

### Module 1 — Object Detection and Comparison

Uses a custom-trained YOLOv8s model to detect and count objects in computer laboratory images. Users can compare automatic predictions with their own observations, identify missed detections, and visualize the differences through statistical charts.

### Module 2 — Voice-Controlled Navigation

Integrates browser-based speech recognition to enable hands-free navigation between application modules using Spanish voice commands.

### Module 3 — Historical Statistics

Provides visual analysis of previously recorded comparisons, allowing users to explore object counts, discrepancies, and detection trends over time.

### Module 4 — Object Detection with Filters and Priorities

Extends the detection workflow with object-class filtering and customizable priorities, allowing users to organize and focus on specific detection results.

## Model and Object Classes

The prototype uses a fine-tuned YOLOv8s model trained on computer laboratory imagery.

The main application works with seven object categories:

`monitor`, `silla`, `mouse`, `teclado`, `laptop`, `torre`, and `persona`.

The original model weights also contain a `mesa` class, but this category is not included in the main comparison and filtering interfaces because of its limited representation in the training data.

The model may produce missed detections, incorrect classifications, or inaccurate counts depending on image quality, lighting, occlusion, and object arrangement.

The original training dataset is not included in this repository.

## Installation

### Requirements

- Docker Desktop with Docker Compose
- Git
- An internet connection for the initial Docker build and dependency installation

No local installation of Python, PHP, or Node.js is required when using the Docker workflow.

### 1. Clone the repository

Clone the repository using HTTPS:

```bash
git clone https://github.com/Bacarrdi/computer-lab-object-detection.git
```

Alternatively, use SSH if you have configured your SSH keys with GitHub:

```bash
git clone git@github.com:Bacarrdi/computer-lab-object-detection.git
```

Then navigate to the project directory:

```bash
cd computer-lab-object-detection
```

### 2. Create the environment file

Create a copy of `.env.example` named `.env`.

On Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

On Linux or macOS:

```bash
cp .env.example .env
```

The application uses file-based sessions and caching. MySQL setup and database migrations are not required for the four implemented modules.

### 3. Build the Docker environment

```bash
docker compose build
```

The image provides PHP, Composer, Python, PyTorch, and the computer vision dependencies.

The first build may require substantial downloads because of PyTorch and the machine learning libraries.

### 4. Install PHP dependencies

```bash
docker compose run --rm --no-deps app composer install --no-scripts
```

### 5. Generate the application key

```bash
docker compose run --rm --no-deps app php artisan key:generate
```

### 6. Register Laravel packages

```bash
docker compose run --rm --no-deps app php artisan package:discover
```

### 7. Start the application

```bash
docker compose up -d
```

Open the application at:

**http://localhost:8001**

To stop the application:

```bash
docker compose down
```

### Important Notes

- The trained model must be available at `archivos_yolo/my_model/my_model.pt`. It is included with the application code.
- Uploaded images, detection results, generated charts, and statistical CSV files are created locally during use.
- The default upload limit is 10 MB per image.

## Local Data and Privacy

The project is intended for local academic demonstration.

Application data is stored in local files. Generated results may be accessible through the local web server.

The current version does not implement user authentication or private file access controls.

**Do not deploy this configuration directly as a public internet service or use it to process sensitive images.**

## Known Limitations

- YOLO predictions are not always accurate, particularly for partially visible or overlapping objects.
- Manual counting is subjective and may contain human errors.
- Count agreement is not equivalent to formal object detection accuracy.
- Model inference runs on CPU and may take time.
- The application uses CSV files instead of a database for historical statistics.
- Abrupt interruptions may leave some generated charts unavailable or outdated.
- Comparison protection relies on Laravel sessions and is not a fully transactional storage system.
- Voice recognition depends on browser compatibility, microphone permissions, and recognition service availability.
- The interface was designed primarily for desktop use.
- The application is not intended for production deployment.

These limitations are documented to reflect the project's academic scope and its opportunities for future improvement.

## Project History and Credits

### Original development — 2025

Originally developed by a team of four Computer Systems Engineering students at the **Universidad Mayor de San Simón (UMSS)**, Cochabamba, Bolivia.

The project was completed during the third-semester course *Métodos, Técnicas y Taller de Programación*, taught by **MSc. Yony Richard Montoya Burgos**.

### Maintenance and partial refactoring — 2026

The project was subsequently revisited for partial refactoring, interface improvements, dependency updates, and Docker-based environment setup, while preserving its original academic purpose and core functionality.

## Project Status

**Academic prototype — local demonstration.**

The repository is published to document the development process, technical decisions, limitations, and learning outcomes of an early university computer vision project.

## License

This project uses [Ultralytics YOLO](https://github.com/ultralytics/ultralytics), which is licensed under the GNU Affero General Public License v3.0 (AGPL-3.0).

The project is distributed under the AGPL-3.0 license. See the [LICENSE](LICENSE) file for details.
