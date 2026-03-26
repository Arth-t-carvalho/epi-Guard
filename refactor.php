<?php

$dir = __DIR__;

function processDir($dir)
{
    if (strpos($dir, 'vendor') !== false || strpos($dir, '.git') !== false) {
        return;
    }

    $files = scandir($dir);

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $path = $dir . DIRECTORY_SEPARATOR . $file;

        if (is_dir($path)) {
            processDir($path);
        }
        else {
            if (pathinfo($path, PATHINFO_EXTENSION) === 'php' || pathinfo($path, PATHINFO_EXTENSION) === 'sql' || pathinfo($path, PATHINFO_EXTENSION) === 'md') {
                if (basename($path) === 'refactor.php')
                    continue;

                $content = file_get_contents($path);
                $newContent = $content;

                // Content replacements
                $replacements = [
                    'Student' => 'Employee',
                    'student' => 'employee',
                    'STUDENT' => 'EMPLOYEE',
                    'Course' => 'Department',
                    'course' => 'department',
                    'COURSE' => 'DEPARTMENT',
                    'Aluno' => 'Funcionario',
                    'aluno' => 'funcionario',
                    'Curso' => 'Setor',
                    'curso' => 'setor',
                    '001_create_courses_table' => '001_create_departments_table',
                    '002_create_students_table' => '002_create_employees_table'
                ];

                foreach ($replacements as $search => $replace) {
                    $newContent = str_replace($search, $replace, $newContent);
                }

                if ($content !== $newContent) {
                    file_put_contents($path, $newContent);
                    echo "Updated content in $path\n";
                }
            }
        }

        // File renaming rename after checking
        $newName = $file;
        $newName = str_replace('Student', 'Employee', $newName);
        $newName = str_replace('student', 'employee', $newName);
        $newName = str_replace('Course', 'Department', $newName);
        $newName = str_replace('course', 'department', $newName);

        if ($newName !== $file) {
            $newPath = $dir . DIRECTORY_SEPARATOR . $newName;
            rename($path, $newPath);
            echo "Renamed $path to $newPath\n";
        }
    }
}

processDir($dir);

echo "Done.\n";
