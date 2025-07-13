package com.example.demo.dto;

import io.swagger.v3.oas.annotations.media.Schema;

import java.time.LocalDateTime;

@Schema(description = "가계코드 히스토리 응답 DTO")
public class HistoryResponse {
    @Schema(description = "가계코드", example = "H001")
    private String householdCode;
    
    @Schema(description = "선택 시간", example = "2024-01-15T14:30:00")
    private LocalDateTime selectedAt;

    public HistoryResponse() {}

    public HistoryResponse(String householdCode, LocalDateTime selectedAt) {
        this.householdCode = householdCode;
        this.selectedAt = selectedAt;
    }

    public String getHouseholdCode() {
        return householdCode;
    }

    public void setHouseholdCode(String householdCode) {
        this.householdCode = householdCode;
    }

    public LocalDateTime getSelectedAt() {
        return selectedAt;
    }

    public void setSelectedAt(LocalDateTime selectedAt) {
        this.selectedAt = selectedAt;
    }
} 