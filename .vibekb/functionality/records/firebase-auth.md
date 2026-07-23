---
id: firebase-auth
type: functionality
title: Firebase authentication
area: authentication
summary: "AuthService signs in with Google, Apple, or email via Firebase Auth, syncs RevenueCat/Mixpanel, and writes users/{uid} profiles."
status: implemented
verification: verified-from-source
user_facing: true
trigger: User taps Google, Apple, or email sign-in / sign-up.
files: [lib/core/auth/auth_service.dart, lib/core/auth/cubit/auth_cubit.dart, lib/features/auth/presentation/screens/email_auth_screen.dart]
reads: []
writes: []
config: []
depends_on: [app-startup]
related_memory: [warning:google-oauth-placeholders, warning:placeholder-api-keys]
created: 2026-07-21
updated: 2026-07-23
tags: []
---

## In one sentence

Firebase Auth is the identity provider; AuthCubit exposes paid/free user states.

## Current behavior

- Google: `GoogleSignIn` + credential to Firebase; iOS/Android client ids from
  `EnvConfig` with `INSERT_YOUR_*` fallbacks.
- Apple: `AppleAuthProvider` via `signInWithProvider`.
- Email: `signInWithEmailAndPassword` / `createUserWithEmailAndPassword`.
- On success: RevenueCat `logIn`, Mixpanel sync, Firestore profile save.

## Failure cases

Missing OAuth client configuration causes Google sign-in failure.

## Current state

Implemented with config risk; verified-from-source.
