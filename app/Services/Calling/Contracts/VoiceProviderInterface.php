<?php

namespace App\Services\Calling\Contracts;

interface VoiceProviderInterface
{
    /**
     * Initiate an outbound voice call.
     */
    public function createCall(string $phoneNumber, int $aiAgentId, string $callPurpose, array $metadata = []): array;

    /**
     * Get current status of a call.
     */
    public function getCallStatus(string $externalCallId): string;

    /**
     * Terminate an active call.
     */
    public function hangupCall(string $externalCallId): bool;

    /**
     * Get audio recording URL.
     */
    public function getRecordingUrl(string $externalCallId): ?string;

    /**
     * Get real-time speech-to-text transcript.
     */
    public function getTranscript(string $externalCallId): ?array;
}
