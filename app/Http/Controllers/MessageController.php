<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use Intervention\Image\Facades\Image;
use Validator;

class MessageController extends Controller
{
    /**
     * @OA\Get(
     *     tags={"Message"},
     *     path="/api/message",
     *     @OA\Response(response="200", description="Messages")
     * )
     */
    public function index() {
        $messages = Message::paginate(25);
        return response()->json($messages, 200,
            ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * @OA\Get(
     *     tags={"Message"},
     *     path="/api/message/{id}",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Message id",
     *         required=true,
     *         @OA\Schema(
     *             type="number",
     *             format="int64"
     *         )
     *     ),
     *   security={{ "bearerAuth": {} }},
     *     @OA\Response(response="200", description="List Messages"),
     * @OA\Response(
     *    response=404,
     *    description="Wrong id",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Wrong message id")
     *        )
     *     )
     * )
     */
    public function getById($id) {
        $message = Message::findOrFail($id);
        return response()->json($message, 200,
            ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * @OA\Post(
     *     tags={"Message"},
     *     path="/api/message",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"user_id", "text"},
     *                 @OA\Property(
     *                     property="image",
     *                     type="file"
     *                 ),
     *                 @OA\Property(
     *                     property="text",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="user_id",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="parent_id",
     *                     type="string"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Add Message.")
     * )
     */
    public function store(Request $request) {
        $input = $request->all();
        $message = array(
            'user_id.required'=>"Enter userId for message",
            'text.required'=>"Enter text of message",
        );
        $validator = Validator::make($input,[
            'user_id' => 'required|exists:users,id',
            'text' => 'required|max:4000',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ], $message);

        if($validator->fails()) {
            return response()->json($validator->errors(), 400,
                ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = uniqid() . '.' . $image->getClientOriginalExtension();
            $sizes = [50, 150, 300, 600, 1200];
            foreach ($sizes as $size)
            {
                $fileSave = $size.'_'.$filename;
                $resizedImage = Image::make($image)->resize($size, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->encode();
                $path = public_path('uploads/' . $fileSave);
                file_put_contents($path, $resizedImage);
            }
            $input['image'] = $filename;
        }
        $message = Message::create($input);
        return response()->json($message, 200,
            ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * @OA\Post(
     *     tags={"Message"},
     *     path="/api/message/edit/{id}",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="number",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                  required={"user_id", "text"},
     *                  @OA\Property(
     *                      property="image",
     *                      type="file"
     *                  ),
     *                  @OA\Property(
     *                      property="text",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="user_id",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="parent_id",
     *                      type="string"
     *                  )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Update Message")
     * )
     */
    public function update($id, Request $request)
    {
        $messaage = Message::findOrFail($id);
        $input = $request->all();
        $message = array(
            'user_id.required'=>"Enter userId for message",
            'text.required'=>"Enter text of message",
        );
        $validator = Validator::make($input,[
            'user_id' => 'required|exists:users,id',
            'text' => 'required|max:4000',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ], $message);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = uniqid() . '.' . $image->getClientOriginalExtension();
            $sizes = [50, 150, 300, 600, 1200];
            foreach ($sizes as $size) {
                $fileDelete = $size.'_'.$messaage->image;
                $removePath = public_path('uploads/' . $fileDelete);
                if (file_exists($removePath)) {
                    unlink($removePath);
                }
            }

            foreach ($sizes as $size)
            {
                $fileSave = $size.'_'.$filename;
                $resizedImage = Image::make($image)->resize($size, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->encode();
                $path = public_path('uploads/' . $fileSave);
                file_put_contents($path, $resizedImage);
            }
            $input['image'] = $filename;
        }
        else {
            $input['image'] = $messaage->image;
        }

        $messaage->update($input);
        return response()->json($messaage, 200,
            ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * @OA\Delete(
     *     path="/api/message/{id}",
     *     tags={"Message"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="number",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Message not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Not authorized"
     *     )
     * )
     */
    public function delete(Request $request, $id) {
        $message = Message::findOrFail($id);
        $sizes = [50, 150, 300, 600, 1200];
        foreach ($sizes as $size) {
            $fileDelete = $size.'_'.$message->image;
            $removePath = public_path('uploads/' . $fileDelete);
            if (file_exists($removePath)) {
                unlink($removePath);
            }
        }
        $message->delete();
        return 204;
    }
}
