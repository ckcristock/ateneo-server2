<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    use ApiResponser;
    public function getData()
    {
        return $this->success(Task::get());
    }

    public function personTask($id)
    {
        $task = DB::table('tasks')
            ->join('people', 'tasks.id_asignador', '=', 'people.id')
            ->where('id_realizador', $id)
            ->get(['tasks.*', 'people.first_name', 'people.second_name', 'people.first_surname']);
        return $this->success($task);
    }

    public function person($companyId)
    {
        $person = DB::table('people')
            ->where('company_id', $companyId)
            ->get(['id', DB::raw('CONCAT_WS(" ",first_name,second_name,first_surname) as name ')]);
        return $this->success($person);
    }

    public function personTaskFor($id)
    {
        $task = DB::table('tasks')
            ->join('people', 'tasks.id_realizador', '=', 'people.id')
            ->where('id_asignador', $id)
            ->get(['tasks.*', 'people.first_name', 'people.second_name', 'people.first_surname']);
        return $this->success($task);
    }

    public function personTaskPendientes($personId)
    {
        $task = DB::table('tasks')
            ->join('people', 'tasks.id_asignador', '=', 'people.id')
            ->where('id_realizador', $personId)
            ->where('estado', 'Pendiente')
            ->get(['tasks.*', 'people.first_name', 'people.second_name', 'people.first_surname']);
        return $this->success($task);
    }

    public function personTaskEjecucion($personId)
    {
        $task = DB::table('tasks')
            ->join('people', 'tasks.id_asignador', '=', 'people.id')
            ->where('id_realizador', $personId)
            ->where('estado', 'En ejecucion')
            ->get(['tasks.*', 'people.first_name', 'people.second_name', 'people.first_surname']);
        return $this->success($task);
    }

    public function personTaskEspera($personId)
    {
        $task = DB::table('tasks')
            ->join('people', 'tasks.id_asignador', '=', 'people.id')
            ->where('id_realizador', $personId)
            ->where('estado', 'En espera')
            ->get(['tasks.*', 'people.first_name', 'people.second_name', 'people.first_surname']);
        return $this->success($task);
    }

    public function personTaskFinalizado($personId)
    {
        $task = DB::table('tasks')
            ->join('people', 'tasks.id_asignador', '=', 'people.id')
            ->where('id_realizador', $personId)
            ->where('estado', 'Finalizado')
            ->get(['tasks.*', 'people.first_name', 'people.second_name', 'people.first_surname']);
        return $this->success($task);
    }

    public function updateFinalizado($id)
    {
        DB::table('tasks')
            ->where('id', $id)
            ->update(['estado' => "Finalizado"]);
    }
    public function updatePendiente($id)
    {
        DB::table('tasks')
            ->where('id', $id)
            ->update(['estado' => "Pendiente"]);
    }
    public function updateEjecucion($id)
    {
        DB::table('tasks')
            ->where('id', $id)
            ->update(['estado' => "En ejecucion"]);
    }
    public function updateEspera($id)
    {
        DB::table('tasks')
            ->where('id', $id)
            ->update(['estado' => "En espera"]);
    }

    public function updateArchivado($id)
    {
        DB::table('tasks')
            ->where('id', $id)
            ->update(['estado' => "Archivada"]);
    }

    public function getArchivada($id)
    {
        $archivado = DB::table('tasks')
            ->join('people', 'tasks.id_realizador', '=', 'people.id')
            ->where('tasks.id_realizador', $id)
            ->where('tasks.estado', 'Archivada')
            ->get(['tasks.*', 'people.first_name', 'people.second_name', 'people.first_surname']);
        return $this->success($archivado);
    }

    public function taskView($id)
    {
        $task = DB::table('tasks')
            ->join('people', 'tasks.id_asignador', '=', 'people.id')
            ->where('tasks.id', $id)
            ->get(['tasks.*', 'people.first_name', 'people.second_name', 'people.first_surname']);
        $task2 = DB::table('tasks')
            ->join('people', 'tasks.id_realizador', '=', 'people.id')
            ->where('tasks.id', $id)
            ->get(DB::raw('CONCAT_WS(" ",first_name,second_name,first_surname) as name '));
        return $this->success($task, $task2);
    }

    public function newComment($comment)
    {
        $commentjson = json_decode($comment);
        $new = DB::table('comentarios_tareas')
            ->insert([
                'id_person' => $commentjson->{'id_person'},
                'fecha' => $commentjson->{'fecha'},
                'comentario' => $commentjson->{'comentario'},
                'id_task' => $commentjson->{'id_task'},
            ]);
        return $this->success($new);
    }
    public function getComments($idTask)
    {
        $comments = DB::table('comentarios_tareas')
            ->join('tasks', 'tasks.id', '=', 'comentarios_tareas.id_task')
            ->join('people', 'people.id', '=', 'comentarios_tareas.id_person')
            ->where('comentarios_tareas.id_task', $idTask)
            ->get(['comentarios_tareas.*', DB::raw('CONCAT_WS(" ",first_name,second_name,first_surname) as name ')]);
        return $this->success($comments);
    }

    public function deleteComment($commentId)
    {
        $delete = DB::table('comentarios_tareas')
            ->where('comentarios_tareas.id', $commentId)
            ->delete();
        return $this->success($delete);
    }

    public function deleteTask($idTask)
    {
        $delete = DB::table('tasks')
            ->where('tasks.id', $idTask)
            ->delete();
        return $this->success($delete);
    }

    public function adjuntosTask($idTask)
    {
        $myArray = [];
        $adjuntos = DB::table('adjuntos_tasks')
            ->join('tasks', 'tasks.id', '=', 'adjuntos_tasks.id_task')
            ->where('tasks.id', $idTask)
            ->get('adjuntos_tasks.*');

        $json = json_decode($adjuntos, true);
        foreach ($json as &$value) {
            $nombre = $value['nombre'];
            $file = (base64_encode(file_get_contents("C:/laragon/www/ateneo-server/main/storage/app/taskmanager/$idTask/$nombre")));
            $value["fileview"] = $file;
            //return array($file);
        }
        /* for ($i = 0; $i < count($json); $i++) {
            $nombre = $json[$i]['nombre'];
            $file = json_encode(base64_encode(file_get_contents("C:/laragon/www/ateneo-server/main/storage/app/taskmanager/$idTask/$nombre")));            
            //$contents = base64_decode(Storage::disk('taskmanager')->get("/$idTask/$nombre"));
            //array_push($json, array('fileview' => "$file"));
            return $json;
        }
         */
        return $this->success($json);
    }

    public function newTask($task)
    {
        $datos = json_decode($task);
        $link = null;
        if ($datos->{'link'}) {
            $link = str_replace('_', '/', $datos->{'link'});
        }
        $insert = DB::table('tasks')
            ->insert([
                'id_realizador' => $datos->{'id_realizador'},
                'tipo' => $datos->{'tipo'},
                'titulo' => $datos->{'titulo'},
                'descripcion' => $datos->{'descripcion'},
                'fecha' => $datos->{'fecha'},
                'adjuntos' => $datos->{'adjuntos'},
                'link' => $link,
                'id_asignador' => $datos->{'id_asignador'},
                'hora' => $datos->{'hora'},
                'estado' => $datos->{'estado'},
            ]);
        $idTask = DB::getPdo()->lastInsertId();
        $uploads_dir = realpath(dirname(getcwd())) . '\storage\framework\adjuntostasks';
        for ($i = 0; $i < count($_FILES); $i++) {
            if (isset($_FILES["file$i"]["name"])) {
                $tipoarchivo = $_FILES["file$i"]["type"];
                $nombrerchivo = $_FILES["file$i"]["name"];
                $tmp_name = $_FILES["file$i"]["tmp_name"];
                //$dirNombre = '\\'.$nombrerchivo;
                //$dir = $uploads_dir.$dirNombre;
                $ruta = Storage::disk('taskmanager')->putFileAs($idTask, $tmp_name, $nombrerchivo);
                $url = Storage::disk('taskmanager')->url("$idTask/$nombrerchivo");
                //Storage::disk('taskmanager')->put("/$idTask/$nombrerchivo", $tmp_name);
                //move_uploaded_file($tmp_name,"$dir");
                //$imagensubida = "$dir";

                $subida = DB::table('adjuntos_tasks')
                    ->insert([
                        'file' => $url,
                        'nombre' => $nombrerchivo,
                        'tipo' => $tipoarchivo,
                        'id_task' => $idTask,
                    ]);
            }
        }
        return $this->success($idTask);
    }
    // agregar tarea -- obtener tareas asignadas -- editar tareas asignadas -- 
}
