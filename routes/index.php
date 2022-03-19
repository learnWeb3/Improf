<?php

define('ROUTES', array(

    array('GET', '/', "static#home", 'home'),

    array('POST', '/login', "sessions#login", 'sessions_login'),

    array('GET', '/users', "users#index", 'users_index'),
    // display nested ressources (wallets)
    array('GET', '/users/[i:id]', "users#show", 'users_show'),
    array('POST', '/users', "users#create", 'users_create'),
    array('PUT', '/users/[i:id]', "users#update", 'users_update'),
    array('DELETE', '/users/[i:id]', "users#destroy", 'users_destroy'),

    array('GET', '/categories', "categories#index", 'categories_index'),
    // display nested ressources formations.
    array('GET', '/categories/[i:id]', "categories#show", 'categories_show'),
    array('POST', '/categories', "categories#create", 'categories_create'),
    array('PUT', '/categories/[i:id]', "categories#update", 'categories_update'),
    array('DELETE', '/categories/[i:id]', "categories#destroy", 'categories_destroy'),

    array('GET', '/formations', "formations#index", 'formations_index'),
    // dislay nested ressources (schedules, schedule details, appointments, reviews)
    array('GET', '/formations/[i:id]', "formations#show", 'formations_show'),
    array('POST', '/formations', "formations#create", 'formations_create'),
    array('PUT', '/formations/[i:id]', "formations#update", 'formations_update'),
    array('DELETE', '/formations/[i:id]', "formations#destroy", 'formations_destroy'),

    array('POST', '/appointments', "appointments#create", 'appointments_create'),
    array('DELETE', '/appointments/[i:id]', "appointments#destroy", 'appointments_destroy'),

    array('POST', '/reviews', "reviews#create", 'reviews_create'),
    array('PUT', '/reviews/[i:id]', "reviews#update", 'reviews_update'),
    array('DELETE', '/reviews/[i:id]', "reviews#destroy", 'reviews_destroy'),

    array('POST', '/schedules', "schedules#create", 'schedules_create'),
    array('PUT', '/schedules/[i:id]', "schedules#update", 'schedules_update'),
    array('DELETE', '/schedules/[i:id]', "schedules#destroy", 'schedules_destroy'),

    array('GET', '/zoom/callback', "zooms#callback", 'zooms_callback'),
    array('GET', '/zoom/authorize', "zooms#authorize", 'zooms_authorize'),

    array('GET', '/facebook/callback', "facebooks#callback", 'facebooks_callback'),
    array('GET', '/facebook/authorize', "facebooks#authorize", 'facebooks_authorize'),

    array('POST', '/meetings', "meetings#create", 'meetings_create'),
    array('DELETE', '/meetings/[i:id]', "meetings#destroy", 'meetings_destroy'),

));
