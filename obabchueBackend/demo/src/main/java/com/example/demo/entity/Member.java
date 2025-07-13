package com.example.demo.entity;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.Id;

@Entity
public class Member {
    @Id
    private String userId;
    @Column
    private String name;
    @Column
    private int phone;
    @Column
    private int gender;
    @Column
    private String password;

    public Member() {}

    public Member(String userId, String password) {
        this.userId = userId;
        this.password = password;
    }

    public Member(String userId, String name, int phone, int gender, String password) {
        this.userId = userId;
        this.name = name;
        this.phone = phone;
        this.gender = gender;
        this.password = password;
    }

    public String getUserId() {
        return userId;
    }

    public void setUserId(String userId) {
        this.userId = userId;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public int getPhone() {
        return phone;
    }

    public void setPhone(int phone) {
        this.phone = phone;
    }

    public int getGender() {
        return gender;
    }

    public void setGender(int gender) {
        this.gender = gender;
    }

    public String getPassword() {
        return password;
    }

    public void setPassword(String password) {
        this.password = password;
    }
}
