"use strict";
export function controllaPassword(password) {
    const regex = /^[A-Z][A-Za-z0-9'$+@]{4,16}$/;
    if (!regex.test(password))
        return false;
    return true;
}