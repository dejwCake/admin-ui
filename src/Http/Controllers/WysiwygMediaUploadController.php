<?php

declare(strict_types=1);

namespace Brackets\AdminUI\Http\Controllers;

use Brackets\AdminUI\Models\WysiwygMedia;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Intervention\Image\Laravel\Facades\Image;
use Spatie\ImageOptimizer\OptimizerChainFactory;

final class WysiwygMediaUploadController extends BaseController
{
    public function upload(Request $request, Filesystem $filesystem, Config $config): JsonResponse
    {
        // get image from request and check validity
        $temporaryFile = $request->file('fileToUpload');
        if (
            !$temporaryFile->isFile()
            || !in_array($temporaryFile->getMimeType(), ['image/png', 'image/jpeg', 'image/gif', 'image/svg+xml'], true)
        ) {
            return response()->json([
                'success' => false,
            ]);
        }

        // generate path that it will be saved to
        $savedPath = $config->get(
            'wysiwyg-media.media_folder',
        ) . '/' . time() . $temporaryFile->getClientOriginalName();

        // create directory in which we will be uploading into
        if (!$filesystem->isDirectory($config->get('wysiwyg-media.media_folder'))) {
            $filesystem->makeDirectory($config->get('wysiwyg-media.media_folder'), 0755, true);
        }

        // resize and save image
        Image::read($temporaryFile->path())
            ->scaleDown($config->get('wysiwyg-media.maximum_image_width'))
            ->save($savedPath);

        // optimize image
        OptimizerChainFactory::create()->optimize($savedPath);

        // create related model
        $wysiwygMedia = WysiwygMedia::create(['file_path' => $savedPath]);

        // return image's path to use in wysiwyg
        return response()->json([
            'file' => url($savedPath),
            'mediaId' => $wysiwygMedia->id,
            'success' => true,
        ]);
    }
}
