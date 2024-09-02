<div id="competitionCarousel" class="carousel slide bg-secondary py-5" data-ride="carousel">
    <div class="container">
        <!-- Заголовок -->
        <h2 class="text-white text-center mb-5">Как создать соревнование?</h2>

        <div class="carousel-inner">
            <!-- Слайд 1 -->
            <div class="carousel-item active">
                <div class="card bg-dark text-white border-0">
                    <div class="card-body d-flex align-items-center justify-content-center"
                         style="background-image: url('/images/how_create_1.png'); background-size: cover; background-position: center; height: 400px;">
                        <div class="overlay d-flex flex-column align-items-center justify-content-center">
                            <h3 class="card-title">Кликнуть создание соревнования</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Слайд 2 -->
            <div class="carousel-item">
                <div class="card bg-dark text-white border-0">
                    <div class="card-body d-flex align-items-center justify-content-center"
                         style="background-image: url('path/to/your-image2.jpg'); background-size: cover; background-position: center; height: 400px;">
                        <div class="overlay d-flex flex-column align-items-center justify-content-center">
                            <h3 class="card-title">Заполнить форму и настроить соревнование</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Слайд 3 -->
            <div class="carousel-item">
                <div class="card bg-dark text-white border-0">
                    <div class="card-body d-flex align-items-center justify-content-center"
                         style="background-image: url('path/to/your-image3.jpg'); background-size: cover; background-position: center; height: 400px;">
                        <div class="overlay d-flex flex-column align-items-center justify-content-center">
                            <h3 class="card-title">Настроить трассы</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Слайд 4 -->
            <div class="carousel-item">
                <div class="card bg-dark text-white border-0">
                    <div class="card-body d-flex align-items-center justify-content-center"
                         style="background-image: url('path/to/your-image4.jpg'); background-size: cover; background-position: center; height: 400px;">
                        <div class="overlay d-flex flex-column align-items-center justify-content-center">
                            <h3 class="card-title">Опубликовать соревнование</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Навигация по карусели -->
        <a class="carousel-control-prev" href="#competitionCarousel" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#competitionCarousel" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>
</div>

<style>
    .carousel-item {
        height: 400px; /* Высота карусели */
    }

    .carousel-item .card {
        height: 100%; /* Высота карточки равна высоте карусели */
    }

    .overlay {
        background-color: rgba(0, 0, 0, 0.5); /* Полупрозрачный черный фон для улучшения читабельности текста */
        padding: 20px;
        border-radius: 10px;
        text-align: center;
    }

    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        filter: invert(1); /* Сделать стрелки белыми */
    }
</style>
