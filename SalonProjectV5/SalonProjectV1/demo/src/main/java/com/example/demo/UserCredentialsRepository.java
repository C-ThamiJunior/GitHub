package com.example.demo;

import org.springframework.data.jpa.repository.JpaRepository;

public interface UserCredentialsRepository extends JpaRepository<UserCredentials, String> {
    UserCredentials findByUsernameAndPassword(String username, String password);
}
