package com.example.demo.repositroy;

import com.example.demo.entity.History;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;

import java.util.List;

public interface HistoryRepository extends JpaRepository<History, Long> {
    
    @Query("SELECT h FROM History h WHERE h.userId = :userId ORDER BY h.selectedAt DESC")
    List<History> findByUserIdOrderBySelectedAtDesc(@Param("userId") String userId);
    
    List<History> findByUserId(String userId);
} 