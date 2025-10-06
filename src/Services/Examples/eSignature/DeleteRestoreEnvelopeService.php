<?php

namespace DocuSign\Services\Examples\eSignature;

use DocuSign\eSign\Model\Folder;
use DocuSign\eSign\Model\FoldersRequest;
use DocuSign\eSign\Model\FoldersResponse;
use DocuSign\Services\SignatureClientService;

class DeleteRestoreEnvelopeService
{
    /**
     * Moves envelope to a different folder
     *
     * @param SignatureClientService $clientService   The client service for eSignature
     * @param string $accountId     The DocuSign Account ID (GUID or short version)
     * @param string $envelopeId    Envelope ID
     * @param string $folderId      Destination Folder ID
     * @param string|null $fromFolderId  From Folder ID (optional)
     *
     * @return \DocuSign\eSign\Model\FoldersResponse
     */
    public static function moveEnvelopeToFolder(
        SignatureClientService $clientService,
        string $accountId,
        string $envelopeId,
        string $folderId,
        string|null $fromFolderId
    ): FoldersResponse {
        $foldersApi = $clientService->getFoldersApi();

        $foldersRequest = new FoldersRequest([
            'from_folder_id' => $fromFolderId,
            'envelope_ids'   => [$envelopeId],
        ]);

        return $foldersApi->moveEnvelopes($accountId, $folderId, $foldersRequest);
    }

    /**
     * Gets all folders in the account
     *
     * @param SignatureClientService $clientService   The client service for eSignature
     * @param string $accountId     The DocuSign Account ID (GUID or short version)
     *
     * @return \DocuSign\eSign\Model\FoldersResponse
     */
    public static function getFolders(SignatureClientService $clientService, string $accountId): FoldersResponse
    {
        $foldersApi = $clientService->getFoldersApi();

        return $foldersApi->callList($accountId, null);
    }

    /**
     * Gets a folder by its name
     *
     * @param array $folders   All folders in the account
     * @param string $targetName     The folder name to search for
     *
     * @return \DocuSign\eSign\Model\Folder|null
     */
    public static function getFolderByName(array $folders, string $targetName): ?Folder
    {
        foreach ($folders as $folder) {
            if ($folder->getName() === $targetName) {
                return $folder;
            }

            $subFolders = $folder->getFolders();
            if (!empty($subFolders)) {
                $nestedFolder = self::getFolderByName($subFolders, $targetName);
                if ($nestedFolder !== null) {
                    return $nestedFolder;
                }
            }
        }

        return null;
    }
}
