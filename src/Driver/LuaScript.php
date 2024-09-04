<?php

namespace Gabrielmoura\LaravelPennantRedis\Driver;

class LuaScript
{
    /**
     * Get the value of a feature flag.
     */
    public const AllStoredFeatures = <<<'LUA'
        local cursor = "0"
        local keys = {}
        repeat
            local result = redis.call("SCAN", cursor, "MATCH", KEYS[1])
            cursor = result[1]
            for _, key in ipairs(result[2]) do
                table.insert(keys, key)
            end
        until cursor == "0"
        return keys
    LUA;

    /**
     * Purge all feature flags.
     */
    public const Purge = <<<'LUA'
            local cursor = "0"
            repeat
                local result = redis.call("SCAN", cursor, "MATCH", KEYS[1])
                cursor = result[1]
                if #result[2] > 0 then
                    redis.call("DEL", unpack(result[2]))
                end
            until cursor == "0"
        LUA;
}
