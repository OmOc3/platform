<?php

namespace App\Shared\Enums;

enum LectureAssetKind: string
{
    case EmbedVideo = 'embed_video';
    case ExternalVideo = 'external_video';
    case AttachmentLink = 'attachment_link';
    case ResourceLink = 'resource_link';
    case TextBlock = 'text_block';
}
