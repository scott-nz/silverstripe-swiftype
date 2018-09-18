<?php

class SwiftypeSiteTree extends SiteTreeExtension {

    public function onAfterPublish(&$original)
    {
        parent::onAfterPublish($original);
        $this->forceSwiftypeIndex();
    }

    public function onAfterUnpublish()
    {
        parent::onAfterUnpublish();
        $this->forceSwiftypeIndex();
    }


    /**
     * According to the documentation of the RestfulService only xml output is supported at this stage.
     * As we need json, we opted to just use standard PHP curl processing.
     *
     * For future proofing purposes, this function returns a boolean value.
     *
     * Note: This request to Swiftype is ignored any time a request is sent for an existing URL (unfortunately). For
     * reindexing edited Pages, we must unfortunately rely on Constant Crawl. This is probably a design choice on
     * Swiftype's part.
     *
     * @return bool
     */
    protected function forceSwiftypeIndex()
    {
        // We don't reindex dev environments.
        if (Director::isDev()) {
            return true;
        }

        $config = SiteConfig::current_site_config();

        $engineSlug = $config->SwiftypeEngineSlug;
        $domainID = $config->SwiftypeDomainID;
        $apiKey = $config->SwiftypeAPIKey;
        if (!$engineSlug) {

            user_error("Swiftype Engine Slug value has not been set. Settings > Swiftype Search > Swiftype Engine Slug", E_USER_WARNING);

            return false;
        }

        if (!$domainID) {
            user_error("Swiftype Domain ID has not been set. Settings > Swiftype Search > Swiftype Domain ID", E_USER_WARNING);
            return false;
        }

        if (!$apiKey) {
            user_error("Swiftype API Key has not been set. Settings > Swiftype Search > Swiftype Production API Key", E_USER_WARNING);
            return false;
        }

        $updateUrl = $this->getOwner()->getAbsoluteLiveLink();

        // Create curl resource.
        $ch = curl_init();

        // Set url.
        curl_setopt($ch, CURLOPT_URL,
            "https://api.swiftype.com/api/v1/engines/{$engineSlug}/domains/{$domainID}/crawl_url.json");

        // Set request method to "PUT".
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");

        // Set headers.
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
        ));

        // Return the transfer as a string.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Set our PUT values.
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
            'auth_token' => $apiKey,
            'url' => $updateUrl,
        )));

        // $output contains the output string.
        $output = curl_exec($ch);

        // Close curl resource to free up system resources.
        curl_close($ch);

        if (!$output) {
            user_error("We got no response from Swiftype for reindexing page: " . $updateUrl, E_USER_WARNING);
            return false;
        }
        $jsonOutput = json_decode($output, true);
        if (!empty($jsonOutput) && array_key_exists('error', $jsonOutput)) {
            $message = $jsonOutput['error'];
            user_error($message, E_USER_WARNING);
            return false;
        }

        return false;
    }
}
