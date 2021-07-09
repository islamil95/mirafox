<?php 

    $pages = [];
    if ($paginator->hasPages()) {
        // Первая и предыдущая страницы
        if (!$paginator->onFirstPage()) {
            $pages[] = [
                "page" => 1,
                "type" => "first",
            ];
            $pages[] = [
                "page" => $paginator->currentPage() - 1,
                "type" => "previous",
            ];        
        }
        // Номера страниц
        foreach ($elements as $element) {
            if (is_array($element)) {
                foreach ($element as $key => $value) {
                    $pages[] = [
                        "page" => $key,
                        "type" => $paginator->currentPage() == $key ? "current" : "page",
                    ];
                }
            } else {
                // Троеточик (пропуск страниц, если их слишком много)
                $pages[] = [
                    "page" => null,
                    "type" => "more",
                ];
            }
        }
        // Следующая и последняя страницы
        if ($paginator->hasMorePages()) {
            $pages[] = [
                "page" => $paginator->currentPage() + 1,
                "type" => "next",
            ];
            $pages[] = [
                "page" => $paginator->lastPage(),
                "type" => "last",
            ];        
        }
    }
    echo json_encode($pages);

?>