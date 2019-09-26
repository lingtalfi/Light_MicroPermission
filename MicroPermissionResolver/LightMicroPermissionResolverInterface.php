<?php


namespace Ling\Light_MicroPermission\MicroPermissionResolver;

/**
 * The LightMicroPermissionResolverInterface interface.
 */
interface LightMicroPermissionResolverInterface
{

    /**
     * Returns the permission corresponding to the given micro-permission identifier.
     * Or false if the micro-permission has no permission assigned yet.
     *
     * @param string $microPermissionId
     * @return string|false
     */
    public function resolve(string $microPermissionId);
}