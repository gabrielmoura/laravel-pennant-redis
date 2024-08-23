<?php

namespace Gabrielmoura\LaravelPennantRedis\Driver;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\Collection;
use Laravel\Pennant\Contracts\CanListStoredFeatures;
use Laravel\Pennant\Contracts\Driver;
use Laravel\Pennant\Events\UnknownFeatureResolved;
use Laravel\Pennant\Feature;
use stdClass;

class RedisFeatureDriver implements CanListStoredFeatures, Driver
{
    /**
     * The sentinel value for unknown features.
     */
    protected stdClass $unknownFeatureValue;

    /**
     * The prefix for the feature flags.
     */
    private string $prefix = 'feature';

    /**
     * Create a new driver instance.
     *
     * @param  array<string, callable(mixed $scope): mixed>  $featureStateResolvers
     */
    public function __construct(
        private readonly RedisManager $redis,
        private readonly Dispatcher $events,
        protected array $featureStateResolvers
    ) {
        $this->unknownFeatureValue = new stdClass;
    }

    /**
     * Define an initial feature flag state resolver.
     */
    public function define(string $feature, callable $resolver): void
    {
        $this->featureStateResolvers[$feature] = $resolver;
    }

    /**
     * Get the names of all defined features.
     *
     * @return array<string>
     */
    public function defined(): array
    {
        return array_keys($this->featureStateResolvers);
    }

    /**
     * Get multiple feature flag values.
     *
     * @param  array<string, array<int, mixed>>  $features
     * @return array<string, array<int, mixed>>
     */
    public function getAll(array $features): array
    {
        return Collection::make($features)
            ->map(fn ($scopes, $feature) => $this->getFeatureValues($feature, $scopes))
            ->all();
    }

    /**
     * Check if the value is valid.
     */
    private function checkValue(mixed $value): bool
    {
        if ($value instanceof $this->unknownFeatureValue) {
            return false;
        }
        if (is_string($value)) {
            return ! ($value == '');
        }

        return true;

    }

    /**
     * Retrieve a feature flag's value.
     */
    public function get(string $feature, mixed $scope): mixed
    {
        $scopeKey = Feature::serializeScope($scope);
        $value = $this->redis->command('HGET', ["$this->prefix:$feature", $scopeKey]);

        if ($this->checkValue($value)) {
            return $value;
        }

        return with($this->resolveValue($feature, $scope), function ($value) use ($feature, $scopeKey) {
            if ($value == $this->unknownFeatureValue) {
                return false;
            }

            $this->set($feature, $scopeKey, $value);

            return $value;
        });
    }

    /**
     * Determine the initial value for a given feature and scope.
     */
    protected function resolveValue($feature, $scope)
    {
        if (! array_key_exists($feature, $this->featureStateResolvers)) {
            $this->events->dispatch(new UnknownFeatureResolved($feature, $scope));

            return $this->unknownFeatureValue;
        }

        return $this->featureStateResolvers[$feature]($scope);
    }

    /**
     * Set a feature flag's value.
     */
    public function set(string $feature, mixed $scope, mixed $value): void
    {
        if ($scope !== null) {
            $scopeKey = Feature::serializeScope($scope);
            $this->redis->command('HSET', ["$this->prefix:$feature", $scopeKey, $value]);
        }
    }

    /**
     * Set a feature flag's value for all scopes.
     */
    public function setForAllScopes(string $feature, mixed $value): void
    {
        $this->redis->command('HMSET', ["$this->prefix:$feature", $value]);
    }

    /**
     * Delete a feature flag's value.
     */
    public function delete(string $feature, mixed $scope): void
    {
        $this->redis->command('HDEL', ["$this->prefix:$feature", Feature::serializeScope($scope)]);
    }

    /**
     * Purge the given feature from storage.
     */
    public function purge(?array $features = null): void
    {
        $featuresToDelete = $features ?? $this->getAllStoredFeatures();

        foreach ($featuresToDelete as $feature) {
            $this->redis->command('DEL', ["$this->prefix:$feature"]);
        }
    }

    /**
     * Get values for a feature for multiple scopes.
     *
     * @param  array<int, mixed>  $scopes
     * @return array<int, mixed>
     */
    protected function getFeatureValues(string $feature, array $scopes): array
    {
        return Collection::make($scopes)
            ->map(fn ($scope) => $this->get($feature, $scope))
            ->all();
    }

    /**
     * Retrieve all stored features from Redis.
     *
     * @return array<string>
     */
    protected function getAllStoredFeatures(): array
    {
        return array_map(
            fn ($key) => substr(strrchr($key, ':'), 1),
            $this->redis->command('KEYS', ["$this->prefix:*"])
        );
    }

    public function stored(): array
    {
        return $this->getAllStoredFeatures();
    }
}
