package com.example.demo.config;

import com.example.demo.entity.Category;
import com.example.demo.repositroy.CategoryRepository;
import org.springframework.boot.CommandLineRunner;
import org.springframework.stereotype.Component;

@Component
public class CategoryDataInitializer implements CommandLineRunner {
    private final CategoryRepository categoryRepository;
    public CategoryDataInitializer(CategoryRepository categoryRepository) {
        this.categoryRepository = categoryRepository;
    }
    @Override
    public void run(String... args) {
        String[] categories = {"한식", "양식", "치킨", "분식", "주점", "카페/디저트", "일식"};
        for (String name : categories) {
            if (categoryRepository.findByName(name) == null) {
                categoryRepository.save(new Category(name));
            }
        }
    }
} 