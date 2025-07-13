package com.example.demo.entity;

import jakarta.persistence.*;
import io.swagger.v3.oas.annotations.media.Schema;

@Entity
@Schema(description = "사용자 가계코드 선택 히스토리")
public class History {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Schema(description = "PK")
    private Long id;

    @Column(name = "user_id", nullable = false)
    @Schema(description = "사용자 ID", example = "user123")
    private String userId;

    @Column(name = "household_code", nullable = false)
    @Schema(description = "선택한 가계코드", example = "H001")
    private String householdCode;

    @Column(name = "selected_at")
    @Schema(description = "선택 시간")
    private java.time.LocalDateTime selectedAt;

    public History() {}

    public History(String userId, String householdCode) {
        this.userId = userId;
        this.householdCode = householdCode;
        this.selectedAt = java.time.LocalDateTime.now();
    }

    public Long getId() {
        return id;
    }

    public void setId(Long id) {
        this.id = id;
    }

    public String getUserId() {
        return userId;
    }

    public void setUserId(String userId) {
        this.userId = userId;
    }

    public String getHouseholdCode() {
        return householdCode;
    }

    public void setHouseholdCode(String householdCode) {
        this.householdCode = householdCode;
    }

    public java.time.LocalDateTime getSelectedAt() {
        return selectedAt;
    }

    public void setSelectedAt(java.time.LocalDateTime selectedAt) {
        this.selectedAt = selectedAt;
    }
} 