#!/bin/sh
# Refuses an image whose Alpine packages miss the November 2026 security
# fixes (nginx CVE-2026-113425/121810/123007, OpenSSL CVE-2026-95691/95692,
# libxml2 CVE-2026-86144). Run at image build time.
set -eu

status=0
for spec in nginx=1.30.7-r0 libssl3=3.5.9-r1 libcrypto3=3.5.9-r1 libxml2=2.13.9-r3; do
    name=${spec%%=*}
    minimum=${spec#*=}
    installed=$(apk info -e -v "$name" 2>/dev/null | sed "s/^$name-//")
    [ -n "$installed" ] || continue # not installed: nothing to patch
    if [ "$(apk version -t "$installed" "$minimum")" = "<" ]; then
        echo "$name $installed rentan, butuh $minimum - bangun ulang image tanpa cache." >&2
        status=1
    fi
done

[ "$status" -eq 0 ] && echo "Paket sistem (nginx, OpenSSL, libxml2) sudah memuat tambalan keamanan terbaru."
exit "$status"
