package com.example.demo.dto;

import io.swagger.v3.oas.annotations.media.Schema;

@Schema(description = "음식점 추천 요청 폼")
public class RecommendForm {
    @Schema(description = "예산", example = "20000")
    private String budget;
    @Schema(description = "음식 카테고리", example = "한식")
    private String foodKategori;

    public RecommendForm(String foodKategori, String budget) {
        this.foodKategori = foodKategori;
        this.budget = budget;
    }

    public String getBudget() {
        return budget;
    }

    public void setBudget(String budget) {
        this.budget = budget;
    }

    public String getFoodKategori() {
        return foodKategori;
    }

    public void setFoodKategori(String foodKategori) {
        this.foodKategori = foodKategori;
    }
}
