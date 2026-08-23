<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadFileRequest;
use App\Models\AuditLog;
use App\Models\File;
use App\Services\AuditLogServiceClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOption\None;

class FileController extends Controller
{
    
    public function __construct(private AuditLogServiceClient $auditlog){}

    public function upload(UploadFileRequest $request):JsonResponse{

        $payload = $request->attributes->get('jwt_payload');
        $sub = $payload->sub;
        $file = $request->file('file');

        $original_name = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $mime_type = $file->getMimeType();
        $size = $file->getSize();
        $stored_name = Str::uuid();

        $directory = now()->format('Y/m');
        $filename = $stored_name . '.' . $extension;

        $full_path = $directory . '/' . $filename;


        $log = null;
        $record = null;
        try {
            DB::transaction(function() use ($sub,$request,$original_name,$stored_name,$extension,$mime_type,$size,$full_path,&$log,&$record){

                $record = File::create([
                    'owner_id'=>$sub,
                    'original_name'=>$original_name,
                    'stored_name'=>$stored_name,
                    'extension'=>$extension,
                    'mime_type'=>$mime_type,
                    'size'=>$size,
                    'path'=>$full_path,
                ]);

                $log = [
                    'actor_id'=>$sub,
                    'service' => 'file-store',
                    'action' => 'file.upload',
                    'resource_type' => 'file',
                    'resource_id' => $record->id,
                    'ip_address' => $request->ip(),
                    'metadata' => [
                        'original_name'=>$original_name,
                        'mime'=>$mime_type,
                        'extension'=>$extension,
                        'path'=>$full_path,
                    ],
                ];


            });
        } catch (\Throwable $th) {
            return response()->json(['message'=>'Cannot upload file'],502);
        }

        try {
            Storage::disk('files')->putFileAs($directory,$file,$filename);
        } catch (\Throwable $th) {
            $record != null ? $record->delete() : null;
            return response()->json(['message'=>'Cannot upload file'],502);
        }

        if ($log != null){
            $this->auditlog->sendLog($log);
        }
        
        return response()->json(['message'=>'File uploaded', 'id'=>$record != null ? $record->id:null],201);
    }

    public function download(Request $request, string $id):mixed{
        
        $record = File::where('id',$id)->first();

        if ($record === null){
            return response()->json(['message'=>'Not found'],404);
        }

        $payload = $request->attributes->get('jwt_payload');
        $sub = $payload->sub;
        $role = (array)$payload->roles;

        if($record->owner_id != $sub && !$this->isAdmin($role)){
            return response()->json(['message'=>'Not authorizate'],403);
        }

        

        if (!Storage::disk('files')->exists($record->path)){
            return response()->json(['message'=>'File not found in disk'],404);
        }

        return response()->file(
            Storage::disk('files')->path($record->path),
            ['Content-Type' => $record->mime_type]
        );

    }

    public function delete(Request $request, string $id):JsonResponse{

        $record = File::where('id',$id)->first();

        if ($record === null){
            return response()->json(['message'=>'Not found'],404);
        }

        $payload = $request->attributes->get('jwt_payload');
        $sub = $payload->sub;
        $role = (array)$payload->roles;

        if(!$this->isAdmin($role)){
            return response()->json(['message'=>'Not authorizate'],403);
        }

        try {
            Storage::disk('files')->delete($record->path);

            $log = [
                'actor_id'=>$sub,
                'service' => 'file-store',
                'action' => 'file.deleted',
                'resource_type' => 'file',
                'resource_id' => $record->id,
                'ip_address' => $request->ip(),
                'metadata'=>['user_agent'=>$request->userAgent()]
            ];
             $record->delete();
        } catch (\Throwable $th) {
            $record->delete();
           return response()->json(['message'=>'File already delete'],200);
        }

        $this->auditlog->sendLog($log);

        return response()->json(['message'=>'File deleted'],200);
    }


    private function isAdmin(array $roles):bool{
        return array_intersect($roles,['super_admin','academic_admin','security_admin']) !== [];
    }
}
