<section class="content__side">
    <h2 class="content__side-heading">Проекты</h2>

    <nav class="main-navigation">
        <ul class="main-navigation__list">
            <?php foreach($projects as $project) : ?>
                <li class="main-navigation__list-item <?php if ($project['selected']): ?>main-navigation__list-item--active<?php endif; ?>">
                    <a class="main-navigation__list-item-link" href="?project_id=<?= $project['id'] ?>"><?= htmlspecialchars($project['name']); ?></a>
                    <span class="main-navigation__list-item-count"><?= htmlspecialchars(countTasks($tasksAll, $project['name'])); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <a class="button button--transparent button--plus content__side-button"
       href="add_project.php" target="project_add">Добавить проект</a>
</section>

<main class="content__main">
    <h2 class="content__main-heading">Список задач</h2>

    <form class="search-form" action="index.php" method="get" autocomplete="off">
        <input class="search-form__input" type="text" name="query" value="<?= $_GET['query'] ?? ''?>" placeholder="Поиск по задачам">

        <input class="search-form__submit" type="submit" name="" value="Искать">
    </form>

    <div class="tasks-controls">
        <nav class="tasks-switch">
            <a href="?project_id=<?= $_GET['project_id'] ?? $projects[0]['id']; ?>&tasks_all=true" class="tasks-switch__item <?php if(isset($_GET['tasks_all'])): ?>tasks-switch__item--active<?php endif; ?>">Все задачи</a>
            <a href="?tasks_today=true" class="tasks-switch__item <?php if(isset($_GET['tasks_today'])): ?>tasks-switch__item--active<?php endif; ?>">Повестка дня</a>
            <a href="?tasks_tomorrow=true" class="tasks-switch__item <?php if(isset($_GET['tasks_tomorrow'])): ?>tasks-switch__item--active<?php endif; ?>">Завтра</a>
            <a href="?tasks_expired=true" class="tasks-switch__item <?php if(isset($_GET['tasks_expired'])): ?>tasks-switch__item--active<?php endif; ?>">Просроченные</a>
        </nav>

        <label class="checkbox">
            <!--добавить сюда атрибут "checked", если переменная $show_complete_tasks равна единице-->
            <input class="checkbox__input visually-hidden show_completed" type="checkbox" <?php if ($show_complete_tasks === 1): ?>checked<?php endif; ?>>

            <span class="checkbox__text">Показывать выполненные</span>
        </label>
    </div>

    <table class="tasks">
        <!-- возвращаем ошибку при отсутствии задач -->
        <?php if (empty($tasks)): ?><p>Ничего не найдено по вашему запросу</p><?php endif; ?>
        <?php foreach($tasks as $task) :
            if ($show_complete_tasks === 0 && $task['isDone']) :
                continue;
            endif; ?>
            <!-- отображение строки с классом task--completed при условии исполнения задания -->
            <!-- отображение строки с классом task--important при условии что дата не null и менее 24 часов до задания -->
            <tr class="tasks__item task <?php if ($task['isDone']): ?>task--completed<?php endif; ?><?php checkHours($hoursBeforeTask, $task['date']); if ((is_string($task['date'])) && (checkHours($hoursBeforeTask, $task['date']))): ?> task--important<?php endif; ?>">
                <td class="task__select">
                    <label class="checkbox task__checkbox">
                        <input class="checkbox__input visually-hidden task__checkbox" type="checkbox" value="<?= htmlspecialchars($task['id']); ?>"
                            <?php if ($task['isDone'] === 1): ?>checked<?php endif; ?>>
                        <span class="checkbox__text "><?= htmlspecialchars($task['name']); ?></span>
                    </label>
                </td>
                <td><a href="/uploads/<?= htmlspecialchars($task['file_name']); ?>"><?= htmlspecialchars($task['file_name']); ?></a></td>
                <td class="task__date"><?= htmlspecialchars($task['date']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</main>
