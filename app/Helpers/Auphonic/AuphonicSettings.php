<?php

namespace App\Helpers\Auphonic;

use App\Helpers\Auphonic\Exceptions\LoudnessTargetNotSupportedException;
use App\Helpers\Auphonic\Exceptions\NoiseReductionAmountNotSupportedException;
use App\Helpers\Auphonic\Exceptions\OutputFileNotSupportedException;
use App\Helpers\Auphonic\Exceptions\ValidationException;

class AuphonicSettings
{
    /**
     * @var string
     */
    protected string $fileFormat = 'mp3';

    /**
     * @var int
     */
    protected int $bitrate = 256;

    /**
     * @var bool
     */
    protected bool $adaptiveLeveler;

    /**
     * @var bool
     */
    protected bool $loudnessNormalization;

    /**
     * @var int
     */
    protected int $loudnessTarget = -16;

    /**
     * @var bool
     */
    protected bool $filtering;

    /**
     * @var bool
     */
    protected bool $noiseAndHumReduction;

    /**
     * @var int
     */
    protected int $noiseReductionAmount = 0;

    /**
     * Default file format is mp3, as this is the most widely accepted file format across distributors
     * such as spotify and apple podcasts.
     *
     * @param  string $format
     * @return $this
     * @throws OutputFileNotSupportedException
     */
    public function setFileFormat(string $format): self
    {
        $fileFormats = [
            'mp3',
            'wav',
            'alac',
            'aac',
        ];

        if (! in_array($format, $fileFormats)) {
            throw new OutputFileNotSupportedException();
        }

        $this->fileFormat = $format;

        return $this;
    }

    /**
     * Noted in kbps.
     * Default for podcasts is 256kbps.
     *
     * @param  int $bitrate
     * @return $this
     * @throws ValidationException
     */
    public function setBitrate(int $bitrate): self
    {
        $bitrates = [
            32, 40, 48, 56, 64, 80, 96, 112,
            128, 160, 192, 224, 256, 320,
        ];

        if (! in_array($bitrate, $bitrates)) {
            throw new ValidationException();
        }

        $this->bitrate = $bitrate;

        return $this;
    }

    /**
     * @param  bool $enabled
     * @return $this
     */
    public function setAdaptiveLeveler(bool $enabled): self
    {
        $this->adaptiveLeveler = $enabled;

        return $this;
    }

    /**
     * @param  bool $enabled
     * @return $this
     */
    public function setLoudnessNormalization(bool $enabled): self
    {
        $this->loudnessNormalization = $enabled;

        return $this;
    }

    /**
     * -16 is the default for podcasts.
     *
     * @param  int $target
     * @return $this
     * @throws LoudnessTargetNotSupportedException
     */
    public function setLoudnessTarget(int $target): self
    {
        $targets = [
            -13, -14, -15, -16, -18, -19,
            -20, -23, -24, -26, -27, -31,
        ];

        if (! in_array($target, $targets)) {
            throw new LoudnessTargetNotSupportedException();
        }

        $this->loudnessTarget = $target;

        return $this;
    }

    /**
     * @param  bool $enabled
     * @return $this
     */
    public function setFiltering(bool $enabled): self
    {
        $this->filtering = $enabled;

        return $this;
    }

    /**
     * @param  bool $enabled
     * @return $this
     */
    public function setNoiseAndHumReduction(bool $enabled): self
    {
        $this->noiseAndHumReduction = $enabled;

        return $this;
    }

    /**
     * Noted in dB.
     * 0 = auto
     * -1 = disable denoise (dehum only)
     * default option for podcast is auto.
     *
     * @param  int $amount
     * @return $this
     * @throws NoiseReductionAmountNotSupportedException
     */
    public function setNoiseReductionAmount(int $amount): self
    {
        $amounts = [
            0, -1, 3, 6, 9, 12,
            15, 18, 24, 30, 100,
        ];

        if (! in_array($amount, $amounts)) {
            throw new NoiseReductionAmountNotSupportedException();
        }

        $this->noiseReductionAmount = $amount;

        return $this;
    }

    /**
     * Validate that all required settings are not null.
     *
     * @return bool
     * @throws ValidationException
     */
    public function validate(): bool
    {
        $emptyFields = [];

        foreach (get_class_vars(get_class($this)) as $key => $setting) {
            if (is_null($this->$key)) {
                $emptyFields[] = $key;
            }
        }

        if (! empty($emptyFields)) {
            $message = 'Settings validation for Auponic failed. Following fields are not set: '.implode(', ', $emptyFields);

            throw new ValidationException($message);
        }

        return true;
    }

    /**
     * @return string
     */
    public function getFileFormat(): string
    {
        return $this->fileFormat;
    }

    /**
     * @return int
     */
    public function getBitrate(): int
    {
        return $this->bitrate;
    }

    /**
     * @return bool
     */
    public function isAdaptiveLeveling(): bool
    {
        return $this->adaptiveLeveler;
    }

    /**
     * @return bool
     */
    public function normalizesLoudness(): bool
    {
        return $this->loudnessNormalization;
    }

    /**
     * @return int
     */
    public function getLoudnessTarget(): int
    {
        return $this->loudnessTarget;
    }

    /**
     * @return bool
     */
    public function isFiltering(): bool
    {
        return $this->filtering;
    }

    /**
     * @return bool
     */
    public function isNoiseAndHumReducing(): bool
    {
        return $this->noiseAndHumReduction;
    }

    /**
     * @return int
     */
    public function getNoiseReductionAmount(): int
    {
        return $this->noiseReductionAmount;
    }
}
